<?php

namespace App\Filament\Tiers\Widgets;

use App\Enums\Tiers\TiersCategory;
use App\Models\Tiers\Tiers;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TiersGlobalStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Nombre total de tiers
        $nbTotal = Tiers::count();

        // Répartition par catégorie
        $nbClients = Tiers::where('category', TiersCategory::Customer)->count();
        $nbFournisseurs = Tiers::where('category', TiersCategory::Supplier)->count();
        $nbProspects = Tiers::where('category', TiersCategory::Other)->count();

        // Tiers créés ce mois
        $nouveauxMois = Tiers::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Total tiers', number_format($nbTotal))
                ->description('Carnet d\'adresses complet')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('info')
                ->chart([30, 35, 40, 45, $nbTotal]),

            Stat::make('Clients', number_format($nbClients))
                ->description('Clients actifs')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Fournisseurs', number_format($nbFournisseurs))
                ->description('Fournisseurs référencés')
                ->descriptionIcon('heroicon-o-truck')
                ->color('warning'),

            Stat::make('Prospects', number_format($nbProspects))
                ->description('Prospects référencés')
                ->descriptionIcon(Heroicon::Users)
                ->color('danger'),

            Stat::make('Nouveaux ce mois', number_format($nouveauxMois))
                ->description('Tiers créés en '.now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
