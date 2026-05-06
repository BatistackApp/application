<?php

namespace App\Services\RH;

use App\Enums\RH\PointageStatus;
use App\Models\RH\Employee;
use App\Models\RH\PointageSession;
use App\Services\Core\DocumentService;
use Carbon\Carbon;

class PointageDocumentGenerator extends DocumentService
{
    public function __construct(
        private PointageCoutCalculator $calculator,
    ) {}

    /**
     * Fiche de pointage hebdomadaire d'un salarié.
     */
    public function fichePointage(PointageSession $session): string
    {
        $session->loadMissing([
            'employee.user',
            'lines.chantier',
            'validator',
        ]);

        $couts = $this->calculator->getCoutSession($session);

        $linesByDate = $session->lines
            ->sortBy('date')
            ->groupBy(fn ($l) => $l->date->format('Y-m-d'));

        $data = [
            'session' => $session,
            'couts' => $couts,
            'linesByDate' => $linesByDate,
            'title' => "Fiche de pointage — {$session->employee->user->name} — {$session->label_semaine}",
        ];

        return $this->generate(
            'pdf.rh.fiche_pointage',
            $data,
            "pointage_{$session->employee->matricule}_{$session->semaine_du->format('Y-W')}",
            'rh/pointages',
        );
    }

    /**
     * Récapitulatif mensuel des heures par chantier.
     */
    public function recapHeuresMois(Employee $employee, Carbon $mois): string
    {
        $sessions = PointageSession::where('employee_id', $employee->id)
            ->whereIn('status', [
                PointageStatus::VALIDATED,
                PointageStatus::IMPUTED,
            ])
            ->whereBetween('semaine_du', [
                $mois->copy()->startOfMonth()->toDateString(),
                $mois->copy()->endOfMonth()->toDateString(),
            ])
            ->with(['lines.chantier', 'lines.session.employee'])
            ->get();

        // Agrégation par chantier
        $parChantier = collect();
        foreach ($sessions as $session) {
            $couts = $this->calculator->getCoutSession($session);
            foreach ($couts['par_chantier'] as $chantierId => $data) {
                if (! $parChantier->has($chantierId)) {
                    $parChantier[$chantierId] = [
                        'chantier' => $data['chantier'],
                        'heures' => 0.0,
                        'main_oeuvre' => 0.0,
                        'trajet' => 0.0,
                        'total' => 0.0,
                    ];
                }
                $parChantier[$chantierId]['heures'] += $data['heures'];
                $parChantier[$chantierId]['main_oeuvre'] += $data['main_oeuvre'];
                $parChantier[$chantierId]['trajet'] += $data['trajet'];
                $parChantier[$chantierId]['total'] += $data['total'];
            }
        }

        $totalHeures = $sessions->flatMap->lines->sum('heures');
        $totalCout = $parChantier->sum('total');

        $data = [
            'employee' => $employee->load('user'),
            'mois' => $mois,
            'parChantier' => $parChantier,
            'totalHeures' => $totalHeures,
            'totalCout' => $totalCout,
            'title' => "Récapitulatif heures — {$employee->user->name} — {$mois->translatedFormat('F Y')}",
        ];

        return $this->generate(
            'pdf.rh.recap_heures_mois',
            $data,
            "recap_{$employee->matricule}_{$mois->format('Y-m')}",
            'rh/recaps',
        );
    }
}
