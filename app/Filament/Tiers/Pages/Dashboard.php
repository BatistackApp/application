<?php

namespace App\Filament\Tiers\Pages;

use App\Filament\Tiers\Widgets\TiersGlobalStatsWidget;
use App\Filament\Tiers\Widgets\TiersParCategorieWidget;
use App\Filament\Tiers\Widgets\TiersRecentWidget;
use App\Filament\Tiers\Widgets\TopClientsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Tableau de bord Tiers';

    public function getWidgets(): array
    {
        return [
            TiersGlobalStatsWidget::class,
            TiersRecentWidget::class,
            TiersParCategorieWidget::class,
            TopClientsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
