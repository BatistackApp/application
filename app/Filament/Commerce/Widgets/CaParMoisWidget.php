<?php

namespace App\Filament\Commerce\Widgets;

use App\Enums\Commerce\DocumentStatus;
use App\Models\Commerce\CommercialDocument;
use DB;
use Filament\Widgets\ChartWidget;

class CaParMoisWidget extends ChartWidget
{
    protected ?string $heading = 'Chiffre d\'affaires';

    protected static ?int $sort = 4;

    protected ?string $description = 'Évolution sur les 12 derniers mois (HT)';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // CA facturé (HT) par mois sur 12 mois glissants
        $data = CommercialDocument::factures()
            ->whereNotIn('status', [DocumentStatus::CANCELLED])
            ->where('date_document', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('YEAR(date_document) as annee'),
                DB::raw('MONTH(date_document) as mois'),
                DB::raw('SUM(total_ht) as total_ht'),
                DB::raw('SUM(total_ttc) as total_ttc'),
            )
            ->groupBy('annee', 'mois')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get()
            ->keyBy(fn ($item) => "{$item->annee}-{$item->mois}");

        // Construire les 12 derniers mois
        $labels = [];
        $caHt = [];
        $caTtc = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = "{$date->year}-{$date->month}";
            $labels[] = $date->translatedFormat('M y');
            $caHt[] = round($data->get($key)?->total_ht ?? 0, 2);
            $caTtc[] = round($data->get($key)?->total_ttc ?? 0, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'CA HT',
                    'data' => $caHt,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => '#10b981',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'CA TTC',
                    'data' => $caTtc,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => '#3b82f6',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) {
                            return value.toLocaleString('fr-FR') + ' €';
                        }",
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.dataset.label + ' : ' +
                                   context.parsed.y.toLocaleString('fr-FR', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                   }) + ' €';
                        }",
                    ],
                ],
            ],
        ];
    }
}
