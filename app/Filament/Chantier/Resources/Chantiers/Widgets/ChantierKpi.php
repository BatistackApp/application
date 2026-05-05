<?php

namespace App\Filament\Chantier\Resources\Chantiers\Widgets;

use App\Models\Chantier\Chantier;
use App\Services\Chantier\ChantierBudgetService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChantierKpi extends StatsOverviewWidget
{
    public ?Chantier $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $service = app(ChantierBudgetService::class);
        $kpis = $service->getKpis($this->record);

        return [
            Stat::make('Budget prévisionnel', number_format($kpis['budget_total'], 2, ',', ' ').' €')
                ->description('Total lignes budget')
                ->color('info'),

            Stat::make('Coût réel engagé', number_format($kpis['cout_reel'], 2, ',', ' ').' €')
                ->description($kpis['en_depassement'] ? '⚠ Dépassement budget' : 'Dans le budget')
                ->color($kpis['en_depassement'] ? 'danger' : 'success'),

            Stat::make('Reste à dépenser', number_format($kpis['reste_a_depenser'], 2, ',', ' ').' €')
                ->description('Budget − Réel')
                ->color($kpis['reste_a_depenser'] < 0 ? 'danger' : 'gray'),

            Stat::make('Avancement global', $kpis['avancement_global'].' %')
                ->description('Consommation : '.$kpis['taux_consommation'].' %')
                ->color(match (true) {
                    $kpis['taux_avancement_vs_conso'] >= 0 => 'success',
                    $kpis['taux_avancement_vs_conso'] >= -10 => 'warning',
                    default => 'danger',
                }),
        ];
    }
}
