<?php

namespace App\Filament\RH\Widgets;

use App\Models\RH\PointageSession;
use App\Services\RH\PointageCoutCalculator;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PointageSessionKpi extends StatsOverviewWidget
{
    public ?PointageSession $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $this->record->loadMissing([
            'lines.session.employee',
            'lines.chantier',
        ]);

        $calculator = app(PointageCoutCalculator::class);
        $couts = $calculator->getCoutSession($this->record);

        $nbChantiers = count($couts['par_chantier']);
        $nbLignes = $this->record->lines->count();
        $nbSaisis = $this->record->lines
            ->filter(fn ($l) => $l->chantier_id !== null)
            ->count();

        return [
            Stat::make('Heures totales', $couts['total_heures'].'h')
                ->description("{$nbSaisis}/{$nbLignes} lignes saisies")
                ->color('info'),

            Stat::make('Coût main d\'œuvre', number_format($couts['main_oeuvre'], 2, ',', ' ').' €')
                ->description('Heures × taux horaire')
                ->color('primary'),

            Stat::make('Indemnités', number_format(
                $couts['grand_deplacement'] + $couts['panier_repas'],
                2, ',', ' '
            ).' €')
                ->description('GD + panier repas')
                ->color('warning'),

            Stat::make('Total imputé', number_format($couts['total'], 2, ',', ' ').' €')
                ->description("{$nbChantiers} chantier(s) concerné(s)")
                ->color('success'),
        ];
    }
}
