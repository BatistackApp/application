<?php

namespace App\Filament\Chantier\Pages;

use App\Filament\Chantier\Widgets\ChantiersBudgetWidget;
use App\Filament\Chantier\Widgets\ChantiersEnCoursWidget;
use App\Filament\Chantier\Widgets\ChantiersEnRetardWidget;
use App\Filament\Chantier\Widgets\ChantiersGlobalStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Tableau de bord';

    public function getWidgets(): array
    {
        return [
            ChantiersGlobalStatsWidget::class,
            ChantiersEnCoursWidget::class,
            ChantiersBudgetWidget::class,
            ChantiersEnRetardWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
