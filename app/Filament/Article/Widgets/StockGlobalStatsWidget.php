<?php

namespace App\Filament\Article\Widgets;

use App\Enums\Article\StockMouvementType;
use App\Models\Stock\StockMouvement;
use DB;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockGlobalStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Valeur totale du stock
        $valeurStock = DB::table('article_warehouse')
            ->join(DB::raw('(
                SELECT
                    article_id,
                    warehouse_id,
                    unit_cost_ht,
                    ROW_NUMBER() OVER (PARTITION BY article_id, warehouse_id ORDER BY created_at DESC) as rn
                FROM stock_mouvements
                WHERE type = "'.StockMouvementType::ENTRY->value.'"
            ) as last_costs'), function ($join) {
                $join->on('article_warehouse.article_id', '=', 'last_costs.article_id')
                    ->on('article_warehouse.warehouse_id', '=', 'last_costs.warehouse_id')
                    ->where('last_costs.rn', '=', 1);
            })
            ->selectRaw('SUM(article_warehouse.actual_stock * last_costs.unit_cost_ht) as total')
            ->value('total') ?? 0;

        // Nombre d'articles en stock
        $nbArticles = DB::table('article_warehouse')
            ->where('actual_stock', '>', 0)
            ->distinct('article_id')
            ->count('article_id');

        // Articles en alerte
        $nbAlertes = DB::table('article_warehouse')
            ->where('actual_stock', '<=', DB::raw('alert_stock'))
            ->where('actual_stock', '>', 0)
            ->count();

        // Valeur mouvements du mois
        $valeurMouvements = StockMouvement::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('type', [
                StockMouvementType::ENTRY,
                StockMouvementType::EXIT,
            ])
            ->selectRaw('SUM(quantity * unit_cost_ht) as total')
            ->value('total') ?? 0;

        return [
            Stat::make('Valeur du stock', number_format($valeurStock, 2, ',', ' ').' €')
                ->description('Valorisation totale')
                ->descriptionIcon('heroicon-o-currency-euro')
                ->color('success'),

            Stat::make('Articles en stock', number_format($nbArticles))
                ->description('Références actives')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),

            Stat::make('Alertes stock', number_format($nbAlertes))
                ->description('Articles sous le seuil')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($nbAlertes > 0 ? 'warning' : 'success'),

            Stat::make('Mouvements du mois', number_format($valeurMouvements, 2, ',', ' ').' €')
                ->description('Entrées + Sorties')
                ->descriptionIcon('heroicon-o-arrows-right-left')
                ->color('primary'),
        ];
    }
}
