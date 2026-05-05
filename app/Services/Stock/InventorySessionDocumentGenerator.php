<?php

namespace App\Services\Stock;

use App\Models\Stock\InventorySession;
use App\Services\Core\DocumentService;

class InventorySessionDocumentGenerator extends DocumentService
{
    /**
     * Fiche de comptage vierge — à imprimer avant le comptage terrain.
     * Les colonnes "Qté comptée" sont vides pour saisie manuscrite.
     */
    public function ficheDeComptage(InventorySession $session): string
    {
        $data = [
            'session' => $session->load(['warehouse', 'creator', 'lines.article.articleCategory']),
            'title' => "Fiche de comptage — {$session->reference}",
        ];

        return $this->generate(
            'pdf.stock.inventory.fiche_comptage',
            $data,
            "fiche_comptage_{$session->reference}",
            'stock/inventory',
        );
    }

    /**
     * Rapport d'écarts — après validation du comptage.
     * Affiche theoretical vs counted et les ajustements générés.
     */
    public function rapportEcarts(InventorySession $session): string
    {
        $lines = $session->lines()
            ->with('article.articleCategory')
            ->get();

        $linesWithDiff = $lines->filter(fn ($l) => $l->counted_quantity !== null
            && $l->counted_quantity != $l->theoretical_quantity);

        $linesOk = $lines->filter(fn ($l) => $l->counted_quantity !== null
            && $l->counted_quantity == $l->theoretical_quantity);

        $data = [
            'session' => $session->load(['warehouse', 'creator', 'validator']),
            'lines' => $lines,
            'linesWithDiff' => $linesWithDiff,
            'linesOk' => $linesOk,
            'totalEcarts' => $linesWithDiff->count(),
            'title' => "Rapport d'écarts — {$session->reference}",
        ];

        return $this->generate(
            'pdf.stock.inventory.rapport_ecarts',
            $data,
            "rapport_ecarts_{$session->reference}",
            'stock/inventory',
        );
    }
}
