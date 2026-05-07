<?php

namespace App\Filament\RH\Widgets;

use App\Enums\RH\PointageStatus;
use App\Enums\RH\TypeHeure;
use App\Models\RH\PointageLine;
use App\Models\RH\PointageSession;
use DB;
use Filament\Widgets\ChartWidget;

class HeuresMoisWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Heures du mois en cours';

    protected ?string $description = 'Répartition par type d\'heure';

    protected function getData(): array
    {
        // Sessions validées/imputées du mois
        $sessionIds = PointageSession::whereIn('status', [
            PointageStatus::VALIDATED,
            PointageStatus::IMPUTED,
        ])
            ->whereMonth('semaine_du', now()->month)
            ->whereYear('semaine_du', now()->year)
            ->pluck('id');

        // Agrégation par type d'heure
        $heuresParType = PointageLine::whereIn('pointage_session_id', $sessionIds)
            ->select('type_heure', DB::raw('SUM(heures) as total'))
            ->groupBy('type_heure')
            ->get()
            ->mapWithKeys(fn ($item) => [
                TypeHeure::from($item->type_heure)->getLabel() => round($item->total, 1),
            ]);

        $labels = $heuresParType->keys()->toArray();
        $data = $heuresParType->values()->toArray();

        // Couleurs par type
        $colors = [];
        foreach ($heuresParType->keys() as $label) {
            $colors[] = match ($label) {
                'Normale' => '#22c55e',
                'Supplémentaire' => '#f59e0b',
                'Nuit' => '#6366f1',
                'Intempérie' => '#94a3b8',
                'Formation' => '#3b82f6',
                'Congés' => '#ef4444',
                'Maladie' => '#dc2626',
                default => '#64748b',
            };
        }

        return [
            'datasets' => [
                [
                    'label' => 'Heures',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return value + ' h'; }",
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.parsed.x.toFixed(1) + ' h';
                        }",
                    ],
                ],
            ],
        ];
    }
}
