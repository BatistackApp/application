<?php

namespace App\Filament\RH\Widgets;

use App\Enums\RH\PointageStatus;
use App\Models\RH\Employee;
use App\Models\RH\PointageSession;
use App\Services\RH\PointageCoutCalculator;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RhGlobalStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $calculator = app(PointageCoutCalculator::class);

        // Nombre de salariés actifs
        $nbSalaries = Employee::active()->count();

        // Sessions validées/imputées du mois en cours
        $sessionsMois = PointageSession::with(['lines.session.employee', 'lines.chantier'])
            ->whereIn('status', [
                PointageStatus::VALIDATED,
                PointageStatus::IMPUTED,
            ])
            ->whereMonth('semaine_du', now()->month)
            ->whereYear('semaine_du', now()->year)
            ->get();

        // Calcul total heures et coût MO
        $totalHeures = 0;
        $totalCoutMo = 0;

        foreach ($sessionsMois as $session) {
            $couts = $calculator->getCoutSession($session);
            $totalHeures += $couts['total_heures'];
            $totalCoutMo += $couts['main_oeuvre'] + $couts['trajet'];
        }

        // Sessions en attente de validation
        $nbEnAttente = PointageSession::where('status', PointageStatus::SUBMITTED)->count();

        return [
            Stat::make('Salariés actifs', number_format($nbSalaries))
                ->description('Employés en activité')
                ->descriptionIcon('heroicon-o-users')
                ->color('info')
                ->chart([5, 7, 6, 8, $nbSalaries]),

            Stat::make('Heures du mois', number_format($totalHeures, 1, ',', ' ').'h')
                ->description('Heures pointées et validées')
                ->descriptionIcon('heroicon-o-clock')
                ->color('success'),

            Stat::make('Coût MO du mois', number_format($totalCoutMo, 2, ',', ' ').' €')
                ->description('Main d\'œuvre + trajet')
                ->descriptionIcon('heroicon-o-currency-euro')
                ->color('warning'),

            Stat::make('En attente', number_format($nbEnAttente))
                ->description('Sessions soumises à valider')
                ->descriptionIcon('heroicon-o-clock')
                ->color($nbEnAttente > 0 ? 'danger' : 'success'),
        ];
    }
}
