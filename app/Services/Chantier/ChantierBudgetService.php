<?php

namespace App\Services\Chantier;

use App\Enums\Chantier\ChantierBudgetType;
use App\Enums\Chantier\ChantierCoutType;
use App\Enums\Chantier\ChantierTaskStatus;
use App\Models\Chantier\Chantier;
use App\Models\Chantier\ChantierCout;
use App\Models\Chantier\ChantierTask;
use App\Notifications\Chantier\BudgetDepassementNotification;
use Illuminate\Support\Collection;

class ChantierBudgetService
{
    /**
     * Retourne tous les KPI du chantier en un seul appel.
     */
    public function getKpis(Chantier $chantier): array
    {
        $budgetTotal = $this->getBudgetTotal($chantier);
        $coutReel = $this->getCoutReel($chantier);
        $resteADepenser = $budgetTotal - $coutReel;
        $tauxConso = $budgetTotal > 0
            ? round(($coutReel / $budgetTotal) * 100, 1)
            : 0;

        return [
            'budget_total' => $budgetTotal,
            'cout_reel' => $coutReel,
            'reste_a_depenser' => $resteADepenser,
            'taux_consommation' => $tauxConso,
            'avancement_global' => $this->getAvancementGlobal($chantier),
            'budget_par_type' => $this->getBudgetParType($chantier),
            'cout_reel_par_type' => $this->getCoutReelParType($chantier),
            'ecart_par_type' => $this->getEcartParType($chantier),
            'en_depassement' => $resteADepenser < 0,
            'taux_avancement_vs_conso' => $this->getEcartAvancementConso($chantier, $tauxConso),
        ];
    }

    /**
     * Somme du budget prévisionnel toutes lignes confondues.
     */
    public function getBudgetTotal(Chantier $chantier): float
    {
        return (float) $chantier->budgetLines()->sum('cout_total');
    }

    /**
     * Somme des coûts réels imputés.
     */
    public function getCoutReel(Chantier $chantier): float
    {
        return (float) $chantier->couts()->sum('montant_ht');
    }

    /**
     * Budget prévisionnel groupé par type.
     */
    public function getBudgetParType(Chantier $chantier): Collection
    {
        $data = $chantier->budgetLines()
            ->selectRaw('type, SUM(cout_total) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return collect(ChantierBudgetType::cases())
            ->mapWithKeys(fn ($type) => [
                $type->value => [
                    'label' => $type->getLabel(),
                    'color' => $type->getColor(),
                    'budget' => (float) ($data[$type->value] ?? 0),
                ],
            ]);
    }

    /**
     * Coûts réels groupés par type (mappé sur les types budget).
     */
    public function getCoutReelParType(Chantier $chantier): Collection
    {
        $data = $chantier->couts()
            ->selectRaw('type, SUM(montant_ht) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        // On mappe les types cout → budget pour comparaison
        $grouped = collect();

        foreach ($data as $coutType => $total) {
            $budgetType = ChantierCoutType::from($coutType)->toBudgetType()->value;
            $grouped[$budgetType] = ($grouped[$budgetType] ?? 0) + (float) $total;
        }

        return $grouped;
    }

    /**
     * Écart budget/réel par type de coût.
     */
    public function getEcartParType(Chantier $chantier): Collection
    {
        $budgetParType = $this->getBudgetParType($chantier);
        $coutParType = $this->getCoutReelParType($chantier);

        return $budgetParType->map(function ($data, $type) use ($coutParType) {
            $reel = (float) ($coutParType[$type] ?? 0);
            $ecart = $data['budget'] - $reel;

            return [
                ...$data,
                'reel' => $reel,
                'ecart' => $ecart,
                'en_depassement' => $ecart < 0,
                'taux_conso' => $data['budget'] > 0
                    ? round(($reel / $data['budget']) * 100, 1)
                    : 0,
            ];
        });
    }

    /**
     * Avancement global pondéré par budget alloué par tâche.
     */
    public function getAvancementGlobal(Chantier $chantier): float
    {
        $tasks = $chantier->tasks()
            ->with('budgetLines')
            ->get();

        if ($tasks->isEmpty()) {
            return 0.0;
        }

        $totalPoids = 0.0;
        $totalPondere = 0.0;

        foreach ($tasks as $task) {
            $poids = $task->budget_alloue;

            // Si aucun budget alloué, poids = 1 (toutes les tâches égales)
            if ($poids <= 0) {
                $poids = 1;
            }

            $totalPoids += $poids;
            $totalPondere += $task->avancement_pct * $poids;
        }

        return $totalPoids > 0
            ? round($totalPondere / $totalPoids, 1)
            : 0.0;
    }

    /**
     * Indicateur de santé : écart entre avancement physique et consommation budgétaire.
     * Positif = on avance plus vite qu'on ne dépense (bon signe).
     * Négatif = on dépense plus vite qu'on n'avance (alerte).
     */
    public function getEcartAvancementConso(Chantier $chantier, ?float $tauxConso = null): float
    {
        $avancement = $this->getAvancementGlobal($chantier);
        $conso = $tauxConso ?? ($this->getBudgetTotal($chantier) > 0
            ? round(($this->getCoutReel($chantier) / $this->getBudgetTotal($chantier)) * 100, 1)
            : 0);

        return round($avancement - $conso, 1);
    }

    /**
     * KPI d'une tâche spécifique.
     */
    public function getTaskKpis(ChantierTask $task): array
    {
        $task->loadMissing('budgetLines');

        $budgetAlloue = $task->budget_alloue;
        $coutReel = (float) $task->chantier
            ->couts()
            ->whereDate('date_imputation', '>=', $task->date_debut)
            ->whereDate('date_imputation', '<=', $task->date_fin)
            ->sum('montant_ht');

        return [
            'budget_alloue' => $budgetAlloue,
            'cout_reel' => $coutReel,
            'reste' => $budgetAlloue - $coutReel,
            'avancement_pct' => $task->avancement_pct,
            'duree_jours' => $task->duree_jours,
            'en_retard' => $task->date_fin->isPast()
                && $task->status !== ChantierTaskStatus::DONE,
        ];
    }

    /**
     * Imputé un coût réel manuellement sur un chantier.
     */
    public function imputerCout(
        Chantier $chantier,
        array $data,
    ): ChantierCout {
        $cout = $chantier->couts()->create([
            ...$data,
            'user_id' => auth()->id(),
            'date_imputation' => $data['date_imputation'] ?? now()->toDateString(),
        ]);

        // Vérification dépassement après imputation
        $budgetTotal = $this->getBudgetTotal($chantier);
        $coutReel = $this->getCoutReel($chantier);

        if ($budgetTotal > 0 && $coutReel > $budgetTotal) {
            $chantier->responsable?->notify(
                new BudgetDepassementNotification($chantier, $budgetTotal, $coutReel)
            );
        }

        return $cout;
    }
}
