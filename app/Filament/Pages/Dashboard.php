<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccesRapidesWidget;
use App\Filament\Widgets\ActiviteRecenteWidget;
use App\Filament\Widgets\GlobalStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Vue d\'ensemble';

    public function getWidgets(): array
    {
        return [
            GlobalStatsWidget::class,
            AccesRapidesWidget::class,
            ActiviteRecenteWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
