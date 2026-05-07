<?php

namespace App\Filament\Widgets;

use App\Enums\Tiers\TiersCategory;
use App\Models\Chantier\Chantier;
use App\Models\RH\Employee;
use App\Models\Tiers\Tiers;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class GlobalStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Chantiers actifs
        $chantiersActifs = Chantier::enCours()->count();

        // Salariés actifs
        $salariesActifs = Employee::active()->count();

        // Clients
        $nbClients = Tiers::where('category', TiersCategory::Customer)->count();

        // Stock - articles en alerte
        $nbAlertes = DB::table('article_warehouse')
            ->where('actual_stock', '<=', DB::raw('alert_stock'))
            ->where('actual_stock', '>', 0)
            ->count();

        return [
            Stat::make('Chantiers actifs', number_format($chantiersActifs))
                ->description('En cours de réalisation')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('success')
                ->url(route('filament.chantier.pages.dashboard')),

            Stat::make('Équipe', number_format($salariesActifs))
                ->description('Salariés en activité')
                ->descriptionIcon('heroicon-o-users')
                ->color('info')
                ->url(route('filament.rh.pages.dashboard')),

            Stat::make('Clients', number_format($nbClients))
                ->description('Carnet clients')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('warning')
                ->url(route('filament.tiers.pages.dashboard')),

            Stat::make('Alertes stock', number_format($nbAlertes))
                ->description('Articles sous le seuil')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($nbAlertes > 0 ? 'danger' : 'success')
                ->url(route('filament.article.pages.dashboard')),
        ];
    }
}
