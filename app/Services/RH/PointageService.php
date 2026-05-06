<?php

namespace App\Services\RH;

use App\Enums\Chantier\ChantierCoutType;
use App\Enums\RH\Periode;
use App\Enums\RH\PointageStatus;
use App\Enums\RH\TypeHeure;
use App\Models\RH\Employee;
use App\Models\RH\PointageLine;
use App\Models\RH\PointageSession;
use App\Models\RH\RhConfiguration;
use App\Models\User;
use App\Notifications\RH\PointageRejeteNotification;
use App\Notifications\RH\PointageValideNotification;
use App\Services\Chantier\ChantierBudgetService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PointageService
{
    public function __construct(
        private PointageCoutCalculator $calculator,
        private ChantierBudgetService $budgetService,
    ) {}

    /**
     * Crée une session de pointage pour une semaine donnée
     * et génère les lignes vides correspondantes.
     */
    public function createSession(
        Employee $employee,
        Carbon|CarbonInterface $semaine,
        ?string $notes = null,
    ): PointageSession {
        try {
            return DB::transaction(function () use ($employee, $semaine, $notes) {
                $lundi = $semaine->copy()->startOfWeek();

                $this->ensureNoExistingSession($employee, $lundi);

                $session = PointageSession::create([
                    'employee_id' => $employee->id,
                    'semaine_du' => $lundi->toDateString(),
                    'status' => PointageStatus::DRAFT,
                    'notes' => $notes,
                ]);

                $this->generateLines($session, $employee, $lundi);

                return $session;
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'semaine_du' => "Une session existe déjà pour la semaine du {$semaine->copy()->startOfWeek()->format('d/m/Y')}.",
            ]);
        }
    }

    /**
     * Soumet une session pour validation.
     */
    public function submit(PointageSession $session): PointageSession
    {
        $this->ensureStatus($session, PointageStatus::DRAFT);

        $this->validateLinesBeforeSubmit($session);

        $session->update([
            'status' => PointageStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        return $session->fresh();
    }

    /**
     * Valide une session — déclenche l'imputation automatique.
     */
    public function validate(PointageSession $session, User $validator): PointageSession
    {
        $this->ensureStatus($session, PointageStatus::SUBMITTED);

        $session->update([
            'status' => PointageStatus::VALIDATED,
            'validated_at' => now(),
            'validated_by' => $validator->id,
        ]);

        return $session->fresh();
    }

    /**
     * Rejette une session avec un motif obligatoire.
     */
    public function reject(
        PointageSession $session,
        User $validator,
        string $reason,
    ): PointageSession {
        $this->ensureStatus($session, PointageStatus::SUBMITTED);

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'rejection_reason' => 'Le motif de rejet est obligatoire.',
            ]);
        }

        $session->update([
            'status' => PointageStatus::REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Notification au salarié
        $session->employee->user->notify(
            new PointageRejeteNotification($session)
        );

        return $session->fresh();
    }

    /**
     * Impute les coûts sur les chantiers concernés.
     * Appelé automatiquement par l'Observer après VALIDATED.
     */
    public function impute(PointageSession $session): PointageSession
    {
        $this->ensureStatus($session, PointageStatus::VALIDATED);

        return DB::transaction(function () use ($session) {
            $session->loadMissing([
                'lines.session.employee',
                'lines.chantier',
            ]);

            $config = RhConfiguration::current();

            foreach ($session->lines as $line) {
                if (! $line->chantier_id || ! $line->type_heure->isImputable()) {
                    continue;
                }

                $cout = $this->calculator->getCoutLigne($line);

                // Coût main d'œuvre + trajet → MAIN_OEUVRE
                $coutMoTotal = $cout['main_oeuvre'] + $cout['trajet'];
                if ($coutMoTotal > 0) {
                    $this->budgetService->imputerCout($line->chantier, [
                        'type' => ChantierCoutType::MAIN_OEUVRE,
                        'designation' => $this->buildDesignationMo($line, $session),
                        'montant_ht' => $coutMoTotal,
                        'date_imputation' => $line->date->toDateString(),
                        'source_type' => PointageLine::class,
                        'source_id' => $line->id,
                    ]);
                }

                // Grand déplacement → DIVERS
                if ($cout['grand_deplacement'] > 0) {
                    $this->budgetService->imputerCout($line->chantier, [
                        'type' => ChantierCoutType::DIVERS,
                        'designation' => "Grand déplacement — {$session->employee->user->name} — {$line->date->format('d/m/Y')}",
                        'montant_ht' => $cout['grand_deplacement'],
                        'date_imputation' => $line->date->toDateString(),
                        'source_type' => PointageLine::class,
                        'source_id' => $line->id,
                    ]);
                }

                // Panier repas → DIVERS
                if ($cout['panier_repas'] > 0) {
                    $this->budgetService->imputerCout($line->chantier, [
                        'type' => ChantierCoutType::DIVERS,
                        'designation' => "Panier repas — {$session->employee->user->name} — {$line->date->format('d/m/Y')}",
                        'montant_ht' => $cout['panier_repas'],
                        'date_imputation' => $line->date->toDateString(),
                        'source_type' => PointageLine::class,
                        'source_id' => $line->id,
                    ]);
                }
            }

            $session->update([
                'status' => PointageStatus::IMPUTED,
                'imputed_at' => now(),
            ]);

            // Notification au salarié
            $session->employee->user->notify(
                new PointageValideNotification($session)
            );

            return $session->fresh();
        });
    }

    /**
     * Remet une session rejetée en brouillon pour correction.
     */
    public function reopen(PointageSession $session): PointageSession
    {
        $this->ensureStatus($session, PointageStatus::REJECTED);

        $session->update([
            'status' => PointageStatus::DRAFT,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return $session->fresh();
    }

    /**
     * Sauvegarde une ligne de pointage.
     */
    public function saveLine(PointageLine $line, array $data): PointageLine
    {
        $this->ensureSessionEditable($line->session);

        // Exclusivité panier_repas / grand_deplacement
        if (! empty($data['grand_deplacement']) && $data['grand_deplacement']) {
            $data['panier_repas'] = false;
        } elseif (! empty($data['panier_repas']) && $data['panier_repas']) {
            $data['grand_deplacement'] = false;
        }

        $line->update($data);

        return $line->fresh();
    }

    /**
     * Génère les lignes vides pour la semaine selon le planning de l'employé.
     */
    private function generateLines(
        PointageSession $session,
        Employee $employee,
        Carbon $lundi,
    ): void {
        $config = RhConfiguration::current();
        $joursIso = $employee->getJoursIso();
        $lines = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $lundi->copy()->addDays($i);
            $jourIso = $date->dayOfWeekIso;

            if (! in_array($jourIso, $joursIso)) {
                continue;
            }

            // Matin
            $lines[] = [
                'pointage_session_id' => $session->id,
                'chantier_id' => null,
                'date' => $date->toDateString(),
                'periode' => Periode::MATIN->value,
                'type_heure' => TypeHeure::NORMALE->value,
                'heures' => $config->heures_matin,
                'heures_trajet' => 0,
                'panier_repas' => false,
                'grand_deplacement' => false,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Après-midi
            $lines[] = [
                'pointage_session_id' => $session->id,
                'chantier_id' => null,
                'date' => $date->toDateString(),
                'periode' => Periode::APREM->value,
                'type_heure' => TypeHeure::NORMALE->value,
                'heures' => $config->heures_aprem,
                'heures_trajet' => 0,
                'panier_repas' => false,
                'grand_deplacement' => false,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        PointageLine::insert($lines);
    }

    /**
     * Vérifie qu'il n'existe pas déjà une session pour cette semaine.
     */
    private function ensureNoExistingSession(Employee $employee, Carbon $lundi): void
    {
        $exists = PointageSession::where('employee_id', $employee->id)
            ->where('semaine_du', $lundi->toDateString())
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'semaine_du' => "Une session existe déjà pour la semaine du {$lundi->format('d/m/Y')}.",
            ]);
        }
    }

    /**
     * Vérifie que la session est dans le statut attendu.
     */
    private function ensureStatus(PointageSession $session, PointageStatus $expected): void
    {
        if ($session->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "Action impossible — statut actuel : {$session->status->getLabel()}.",
            ]);
        }
    }

    /**
     * Vérifie que la session est modifiable.
     */
    private function ensureSessionEditable(PointageSession $session): void
    {
        if (! in_array($session->status, [PointageStatus::DRAFT, PointageStatus::REJECTED])) {
            throw ValidationException::withMessages([
                'status' => 'La session ne peut plus être modifiée.',
            ]);
        }
    }

    /**
     * Valide les lignes avant soumission.
     * Au moins une ligne doit avoir un chantier et des heures > 0.
     */
    private function validateLinesBeforeSubmit(PointageSession $session): void
    {
        $hasValidLine = $session->lines()
            ->whereNotNull('chantier_id')
            ->where('heures', '>', 0)
            ->exists();

        if (! $hasValidLine) {
            throw ValidationException::withMessages([
                'lines' => 'La session doit contenir au moins une ligne avec un chantier et des heures saisies.',
            ]);
        }

        // Vérification dépassement 24h par jour
        $heuresParJour = $session->lines()
            ->selectRaw('date, SUM(heures) as total_heures')
            ->groupBy('date')
            ->get();

        foreach ($heuresParJour as $jour) {
            if ((float) $jour->total_heures > 24) {
                throw ValidationException::withMessages([
                    'lines' => "Le {$jour->date} dépasse 24h de travail ({$jour->total_heures}h saisies).",
                ]);
            }
        }
    }

    /**
     * Construit la désignation d'une ligne d'imputation MO.
     */
    private function buildDesignationMo(PointageLine $line, PointageSession $session): string
    {
        $nom = $session->employee->user->name;
        $date = $line->date->format('d/m/Y');
        $periode = $line->periode->getLabel();
        $type = $line->type_heure->getLabel();

        $base = "{$nom} — {$date} {$periode} ({$type}) — {$line->heures}h";

        if ((float) $line->heures_trajet > 0) {
            $base .= " + {$line->heures_trajet}h trajet";
        }

        return $base;
    }
}
