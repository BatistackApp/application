<?php

namespace App\Filament\Tiers\Widgets;

use App\Enums\Tiers\TiersCategory;
use App\Models\Tiers\Tiers;
use Filament\Widgets\ChartWidget;

class TiersParCategorieWidget extends ChartWidget
{
    protected ?string $heading = 'Tiers Par Categorie Widget';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $categories = Tiers::select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($item) => [
                TiersCategory::from($item->category->value)->getLabel() => $item->total,
            ]);

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de tiers',
                    'data' => $categories->values()->toArray(),
                    'backgroundColor' => [
                        '#22c55e', // Customer - vert
                        '#f59e0b', // Supplier - orange
                        '#3b82f6', // Prospect - bleu
                        '#6366f1', // Partner - indigo
                    ],
                ],
            ],
            'labels' => $categories->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
