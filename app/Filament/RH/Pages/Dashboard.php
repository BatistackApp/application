<?php

namespace App\Filament\RH\Pages;

use App\Filament\RH\Widgets\HeuresMoisWidget;
use App\Filament\RH\Widgets\PointagesEnAttenteWidget;
use App\Filament\RH\Widgets\RhGlobalStatsWidget;
use App\Filament\RH\Widgets\TopChantiersRhWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Tableau de bord';

    public function getWidgets(): array
    {
        return [
            RhGlobalStatsWidget::class,
            PointagesEnAttenteWidget::class,
            HeuresMoisWidget::class,
            TopChantiersRhWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
