<?php

namespace App\Services\Compta;

use App\Enums\Compta\RegimeTva;
use App\Models\Compta\DeclarationTva;
use App\Models\Compta\ExerciceComptable;
use Illuminate\Validation\ValidationException;

class DeclarationTvaService
{
    public function __construct(
        protected TvaCalculatorService $tvaCalculator
    ) {}

    /**
     * Génère une déclaration de TVA pour une période donnée.
     */
    public function generer(
        ExerciceComptable $exercice,
        RegimeTva $regime,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin
    ): DeclarationTva {
        // Vérifier qu'aucune déclaration n'existe déjà pour cette période
        $periode = $this->formatPeriode($dateDebut, $regime);

        $existing = DeclarationTva::where('exercice_comptable_id', $exercice->id)
            ->where('periode', $periode)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'periode' => "Une déclaration existe déjà pour la période {$periode}.",
            ]);
        }

        // Calculer TVA collectée
        $tvaCollectee = $this->tvaCalculator->getTvaCollectee($dateDebut, $dateFin);

        // Calculer TVA déductible
        $tvaDeductible = $this->tvaCalculator->getTvaDeductible($dateDebut, $dateFin);

        // TVA nette
        $tvaNette = $tvaCollectee['total'] - $tvaDeductible['total'];

        // Crédit période précédente (si existe)
        $creditPrecedent = $this->getCreditPeriodePrecedente($exercice, $dateDebut, $regime);

        // TVA due
        $tvaDue = $tvaNette - $creditPrecedent;

        return DeclarationTva::create([
            'exercice_comptable_id' => $exercice->id,
            'periode' => $periode,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'regime' => $regime,
            'base_tva_collectee_20' => $tvaCollectee['base_20'],
            'montant_tva_collectee_20' => $tvaCollectee['montant_20'],
            'base_tva_collectee_10' => $tvaCollectee['base_10'],
            'montant_tva_collectee_10' => $tvaCollectee['montant_10'],
            'base_tva_collectee_55' => $tvaCollectee['base_55'],
            'montant_tva_collectee_55' => $tvaCollectee['montant_55'],
            'total_tva_collectee' => $tvaCollectee['total'],
            'tva_deductible_immobilisations' => $tvaDeductible['immobilisations'],
            'tva_deductible_biens_services' => $tvaDeductible['biens_services'],
            'total_tva_deductible' => $tvaDeductible['total'],
            'tva_nette' => $tvaNette,
            'credit_periode_precedente' => $creditPrecedent,
            'tva_due' => $tvaDue,
        ]);
    }

    /**
     * Génère les déclarations automatiques pour un exercice selon le régime.
     */
    public function genererAutomatique(ExerciceComptable $exercice, RegimeTva $regime): array
    {
        if (! $regime->isPeriodique()) {
            throw ValidationException::withMessages([
                'regime' => 'Le régime franchise ne nécessite pas de déclarations périodiques.',
            ]);
        }

        $declarations = [];
        $periodes = $this->getPeriodesPourExercice($exercice, $regime);

        foreach ($periodes as [$debut, $fin]) {
            // Ne générer que si la période est terminée
            if ($fin->isFuture()) {
                continue;
            }

            $declarations[] = $this->generer($exercice, $regime, $debut, $fin);
        }

        return $declarations;
    }

    /**
     * Valide une déclaration de TVA.
     */
    public function valider(DeclarationTva $declaration): DeclarationTva
    {
        if (! $declaration->canBeValidated()) {
            throw ValidationException::withMessages([
                'declaration' => 'Cette déclaration ne peut pas être validée.',
            ]);
        }

        $declaration->update([
            'validee' => true,
            'validee_at' => now(),
            'validee_by' => auth()->id(),
        ]);

        return $declaration->fresh();
    }

    /**
     * Marque une déclaration comme transmise.
     */
    public function marquerTransmise(DeclarationTva $declaration): DeclarationTva
    {
        if (! $declaration->canBeTransmitted()) {
            throw ValidationException::withMessages([
                'declaration' => 'Cette déclaration ne peut pas être marquée comme transmise.',
            ]);
        }

        $declaration->update([
            'transmise' => true,
            'transmise_at' => now(),
        ]);

        return $declaration->fresh();
    }

    /**
     * Récupère le crédit de la période précédente.
     */
    protected function getCreditPeriodePrecedente(
        ExerciceComptable $exercice,
        \DateTimeInterface $dateDebut,
        RegimeTva $regime
    ): float {
        // Trouver la déclaration précédente
        $precedente = DeclarationTva::where('exercice_comptable_id', $exercice->id)
            ->where('regime', $regime)
            ->where('date_fin', '<', $dateDebut)
            ->orderByDesc('date_fin')
            ->first();

        if (! $precedente || ! $precedente->validee) {
            return 0;
        }

        // Si la déclaration précédente a un crédit (TVA nette négative)
        return $precedente->tva_nette < 0 ? abs($precedente->tva_nette) : 0;
    }

    /**
     * Formate la période selon le régime.
     */
    protected function formatPeriode(\DateTimeInterface $date, RegimeTva $regime): string
    {
        return match ($regime) {
            RegimeTva::REEL_NORMAL => $date->format('Y-m'), // Mensuel : 2026-05
            RegimeTva::REEL_SIMPLIFIE => $date->format('Y').'-T'.ceil($date->format('n') / 3), // Trimestriel : 2026-T2
            default => $date->format('Y'),
        };
    }

    /**
     * Génère les périodes de déclaration pour un exercice.
     */
    protected function getPeriodesPourExercice(ExerciceComptable $exercice, RegimeTva $regime): array
    {
        $periodes = [];
        $current = clone $exercice->date_debut;

        if ($regime === RegimeTva::REEL_NORMAL) {
            // Mensuel
            while ($current <= $exercice->date_fin) {
                $debut = clone $current;
                $fin = (clone $debut)->endOfMonth();

                if ($fin > $exercice->date_fin) {
                    $fin = $exercice->date_fin;
                }

                $periodes[] = [$debut, $fin];
                $current->addMonth()->startOfMonth();
            }
        } else {
            // Trimestriel
            while ($current <= $exercice->date_fin) {
                $debut = clone $current;
                $fin = (clone $debut)->addMonths(2)->endOfMonth();

                if ($fin > $exercice->date_fin) {
                    $fin = $exercice->date_fin;
                }

                $periodes[] = [$debut, $fin];
                $current->addMonths(3)->startOfMonth();
            }
        }

        return $periodes;
    }

    /**
     * Exporte la déclaration au format CA3 (simplifié).
     */
    public function exporterCa3(DeclarationTva $declaration): array
    {
        return [
            'periode' => $declaration->periode,
            'regime' => $declaration->regime->getLabel(),

            // Bloc A : TVA collectée
            'a1_base_20' => $declaration->base_tva_collectee_20,
            'a1_tva_20' => $declaration->montant_tva_collectee_20,
            'a2_base_10' => $declaration->base_tva_collectee_10,
            'a2_tva_10' => $declaration->montant_tva_collectee_10,
            'a3_base_55' => $declaration->base_tva_collectee_55,
            'a3_tva_55' => $declaration->montant_tva_collectee_55,
            'a_total' => $declaration->total_tva_collectee,

            // Bloc B : TVA déductible
            'b1_immo' => $declaration->tva_deductible_immobilisations,
            'b2_biens' => $declaration->tva_deductible_biens_services,
            'b_total' => $declaration->total_tva_deductible,

            // Bloc C : TVA nette
            'c1_tva_nette' => $declaration->tva_nette,
            'c2_credit_precedent' => $declaration->credit_periode_precedente,
            'c3_tva_due' => $declaration->tva_due,

            // Métadonnées
            'validee' => $declaration->validee,
            'validee_at' => $declaration->validee_at?->format('d/m/Y H:i'),
            'transmise' => $declaration->transmise,
            'transmise_at' => $declaration->transmise_at?->format('d/m/Y H:i'),
        ];
    }
}
