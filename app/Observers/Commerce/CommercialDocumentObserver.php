<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\CommercialDocument;
use Log;

class CommercialDocumentObserver
{
    public function created(CommercialDocument $document): void
    {
        $this->recalculateTotals($document);
    }

    public function updated(CommercialDocument $document): void
    {
        $this->recalculateTotals($document);
        if ($document->wasChanged(['status'])) {
            Log::info("Document {$document->reference} : statut changé vers {$document->status->value}");
        }
    }

    /**
     * Recalcule les totaux HT, TVA, TTC du document.
     */
    protected function recalculateTotals(CommercialDocument $document): void
    {
        $lines = $document->lines;

        if ($lines->isEmpty()) {
            return;
        }

        $totalHt = $lines->sum('total_ht');
        $totalTva = $lines->sum('total_tva');
        $totalTtc = $lines->sum('total_ttc');

        // Application remise globale
        if ($document->remise_globale_pct > 0) {
            $remise = round($totalHt * ($document->remise_globale_pct / 100), 2);
            $totalHt -= $remise;
            $totalTva = round($totalHt * 0.20, 2); // Recalcul TVA après remise
            $totalTtc = $totalHt + $totalTva;
        }

        if ($document->remise_globale_montant > 0) {
            $totalHt -= $document->remise_globale_montant;
            $totalTva = round($totalHt * 0.20, 2);
            $totalTtc = $totalHt + $totalTva;
        }

        $document->updateQuietly([
            'total_ht' => round($totalHt, 2),
            'total_tva' => round($totalTva, 2),
            'total_ttc' => round($totalTtc, 2),
        ]);
    }
}
