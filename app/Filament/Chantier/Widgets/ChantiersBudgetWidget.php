<?php

namespace App\Filament\Chantier\Widgets;

use App\Models\Chantier\Chantier;
use App\Models\Chantier\ChantierBudgetLine;
use App\Models\Chantier\ChantierCout;
use App\Services\Chantier\ChantierBudgetService;
use Filament\Widgets\ChartWidget;

class ChantiersBudgetWidget extends ChartWidget
{
    protected ?string $heading = 'Chantiers Budget Widget';

    protected static ?int $sort = 3;

    protected ?string $description = 'Budget prévisionnel vs coût réel';

    protected function getData(): array
    {
        $budgetService = app(ChantierBudgetService::class);

        $chantiersQuery = Chantier::enCours()
            ->with(['budgetLines']) // Garder pour l'hydratation si nécessaire
            ->addSelect([
                'budget_total' => ChantierBudgetLine::selectRaw('SUM(count_total)')
                    ->whereColumn('chantier_id', 'chantiers.id')
                    ->limit(1), // Ou une sous-requête plus complexe si getBudgetTotal est plus complexe
                'cout_reel' => ChantierCout::selectRaw('SUM(montant_ht)') // Exemple, adapter au modèle réel des coûts
                    ->whereColumn('chantier_id', 'chantiers.id')
                    ->limit(1),
            ])
            ->orderByDesc('budget_total')
            ->take(5);

        $chantiers = $chantiersQuery->get();

        $labels = [];
        $budgets = [];
        $reels = [];

        foreach ($chantiers as $chantier) {
            $labels[] = $chantier->reference;
            $budgets[] = round($budgetService->getBudgetTotal($chantier), 2);
            $reels[] = round($budgetService->getCoutReel($chantier), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Budget prévu',
                    'data' => $budgets,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#2563eb',
                ],
                [
                    'label' => 'Coût réel',
                    'data' => $reels,
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return value.toLocaleString('fr-FR') + ' €'; }",
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
                            return context.dataset.label + ': ' +
                                   context.parsed.y.toLocaleString('fr-FR') + ' €';
                        }",
                    ],
                ],
            ],
        ];
    }
}
