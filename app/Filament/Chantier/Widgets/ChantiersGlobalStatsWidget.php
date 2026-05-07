<?php

namespace App\Filament\Chantier\Widgets;

use App\Models\Chantier\Chantier;
use App\Services\Chantier\ChantierBudgetService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChantiersGlobalStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $budgetService = app(ChantierBudgetService::class);

        // Chantiers actifs (OPEN, ACTIVE, PAUSED)
        $chantiersActifs = Chantier::enCours()->get();
        $nbActifs = $chantiersActifs->count();

        // Budget total des chantiers en cours
        $budgetTotal = $chantiersActifs->sum(function ($chantier) use ($budgetService) {
            return $budgetService->getBudgetTotal($chantier);
        });

        // Coût réel engagé
        $coutReel = $chantiersActifs->sum(function ($chantier) use ($budgetService) {
            return $budgetService->getCoutReel($chantier);
        });

        // Taux de consommation moyen
        $tauxMoyen = 0;
        if ($nbActifs > 0 && $budgetTotal > 0) {
            $tauxMoyen = round(($coutReel / $budgetTotal) * 100, 1);
        }

        return [
            Stat::make('Chantiers actifs', number_format($nbActifs))
                ->description('Ouverts, en cours, en pause')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('info')
                ->chart([3, 5, 4, 6, $nbActifs]),

            Stat::make('Budget total', number_format($budgetTotal, 2, ',', ' ').' €')
                ->description('Budget prévisionnel en cours')
                ->descriptionIcon('heroicon-o-currency-euro')
                ->color('success'),

            Stat::make('Coût réel engagé', number_format($coutReel, 2, ',', ' ').' €')
                ->description('Imputations réelles')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($coutReel > $budgetTotal ? 'danger' : 'warning'),

            Stat::make('Taux de consommation', $tauxMoyen.' %')
                ->description('Moyenne des chantiers actifs')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color(match (true) {
                    $tauxMoyen > 100 => 'danger',
                    $tauxMoyen > 80 => 'warning',
                    default => 'success',
                }),
        ];
    }
}
