<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AccesRapidesWidget extends Widget
{
    protected string $view = 'filament.widgets.acces-rapides-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'links' => [
                [
                    'label' => 'Nouveau chantier',
                    'icon' => 'heroicon-o-plus-circle',
                    'color' => 'success',
                    'url' => route('filament.chantier.resources.chantiers.create'),
                ],
                [
                    'label' => 'Nouveau tiers',
                    'icon' => 'heroicon-o-user-plus',
                    'color' => 'info',
                    'url' => route('filament.tiers.resources.tiers.create'),
                ],
                [
                    'label' => 'Mouvements stock',
                    'icon' => 'heroicon-o-arrows-right-left',
                    'color' => 'warning',
                    'url' => route('filament.article.resources.article.articles.index'),
                ],
                [
                    'label' => 'Valider pointages',
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'primary',
                    'url' => route('filament.rh.resources.r-h.pointage-sessions.index'),
                ],
            ],
        ];
    }
}
