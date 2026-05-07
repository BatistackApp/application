<?php

namespace App\Filament\Article\Pages;

use App\Filament\Article\Widgets\InventairesEnCoursWidget;
use App\Filament\Article\Widgets\MouvementsRecentsWidget;
use App\Filament\Article\Widgets\StockAlertsWidget;
use App\Filament\Article\Widgets\StockGlobalStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Tableau de bord Stock';

    public function getWidgets(): array
    {
        return [
            StockGlobalStatsWidget::class,
            StockAlertsWidget::class,
            MouvementsRecentsWidget::class,
            InventairesEnCoursWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
