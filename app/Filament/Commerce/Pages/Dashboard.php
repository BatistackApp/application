<?php

namespace App\Filament\Commerce\Pages;

use App\Filament\Commerce\Widgets\CaParMoisWidget;
use App\Filament\Commerce\Widgets\CommerceGlobalStatsWidget;
use App\Filament\Commerce\Widgets\DevisEnCoursWidget;
use App\Filament\Commerce\Widgets\FacturesImpayeesWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Tableau de bord';

    public function getWidgets(): array
    {
        return [
            CommerceGlobalStatsWidget::class,
            CaParMoisWidget::class,
            DevisEnCoursWidget::class,
            FacturesImpayeesWidget::class,

        ];
    }
}
