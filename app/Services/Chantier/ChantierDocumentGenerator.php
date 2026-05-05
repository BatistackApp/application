<?php

namespace App\Services\Chantier;

use App\Models\Chantier\Chantier;
use App\Services\Core\DocumentService;

class ChantierDocumentGenerator extends DocumentService
{
    public function __construct(
        private ChantierBudgetService $budgetService,
    ) {}

    /**
     * Rapport de rentabilité complet du chantier.
     */
    public function rapportRentabilite(Chantier $chantier): string
    {
        $chantier->loadMissing([
            'client',
            'responsable',
            'budgetLines',
            'couts',
            'tasks',
        ]);

        $kpis = $this->budgetService->getKpis($chantier);

        $data = [
            'chantier' => $chantier,
            'kpis' => $kpis,
            'title' => "Rapport de rentabilité — {$chantier->reference}",
        ];

        return $this->generate(
            'pdf.chantier.rapport_rentabilite',
            $data,
            "rentabilite_{$chantier->reference}",
            'chantier',
            'landscape',
        );
    }

    /**
     * Fiche budget prévisionnelle par type de coût.
     */
    public function ficheBudget(Chantier $chantier): string
    {
        $chantier->loadMissing(['client', 'responsable', 'budgetLines.article']);

        $budgetParType = $chantier->budgetLines
            ->groupBy('type');

        $data = [
            'chantier' => $chantier,
            'budgetParType' => $budgetParType,
            'totalBudget' => $this->budgetService->getBudgetTotal($chantier),
            'title' => "Fiche budget — {$chantier->reference}",
        ];

        return $this->generate(
            'pdf.chantier.fiche_budget',
            $data,
            "budget_{$chantier->reference}",
            'chantier',
        );
    }
}
