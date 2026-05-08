<?php

namespace App\Services\Commerce;

use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\CommercialDocumentLine;

class CommercialCalculator
{
    /**
     * Calcule les totaux d'une ligne.
     */
    public function calculateLineTotals(CommercialDocumentLine $line): array
    {
        $montantBrut = $line->quantite * $line->prix_unitaire_ht;

        // Application remise ligne
        $remiseMontant = $line->remise_montant;
        $remisePct = ($montantBrut * $line->remise_pct) / 100;

        $totalHt = $montantBrut - $remiseMontant - $remisePct;
        $totalTva = ($totalHt * $line->taux_tva) / 100;
        $totalTtc = $totalHt + $totalTva;

        return [
            'montant_brut' => round($montantBrut, 2),
            'remise_totale' => round($remiseMontant + $remisePct, 2),
            'total_ht' => round($totalHt, 2),
            'total_tva' => round($totalTva, 2),
            'total_ttc' => round($totalTtc, 2),
        ];
    }

    /**
     * Calcule les totaux d'un document.
     */
    public function calculateDocumentTotals(CommercialDocument $document): array
    {
        $lines = $document->lines;

        if ($lines->isEmpty()) {
            return [
                'total_ht' => 0,
                'total_tva' => 0,
                'total_ttc' => 0,
                'par_taux_tva' => [],
            ];
        }

        // Totaux lignes
        $totalHt = 0;
        $totalTva = 0;
        $parTauxTva = [];

        foreach ($lines as $line) {
            $totaux = $this->calculateLineTotals($line);
            $totalHt += $totaux['total_ht'];
            $totalTva += $totaux['total_tva'];

            $taux = (string) $line->taux_tva;
            if (! isset($parTauxTva[$taux])) {
                $parTauxTva[$taux] = [
                    'taux' => $line->taux_tva,
                    'base_ht' => 0,
                    'montant_tva' => 0,
                ];
            }

            $parTauxTva[$taux]['base_ht'] += $totaux['total_ht'];
            $parTauxTva[$taux]['montant_tva'] += $totaux['total_tva'];
        }

        // Application remise globale
        if ($document->remise_globale_pct > 0) {
            $remise = ($totalHt * $document->remise_globale_pct) / 100;
            $totalHt -= $remise;

            // Recalcul TVA proportionnellement
            foreach ($parTauxTva as &$detail) {
                $ratio = $detail['base_ht'] / ($totalHt + $remise);
                $detail['base_ht'] -= ($remise * $ratio);
                $detail['montant_tva'] = ($detail['base_ht'] * $detail['taux']) / 100;
            }

            $totalTva = array_sum(array_column($parTauxTva, 'montant_tva'));
        }

        if ($document->remise_globale_montant > 0) {
            $remise = $document->remise_globale_montant;
            $totalHt -= $remise;

            // Recalcul TVA proportionnellement
            foreach ($parTauxTva as &$detail) {
                $ratio = $detail['base_ht'] / ($totalHt + $remise);
                $detail['base_ht'] -= ($remise * $ratio);
                $detail['montant_tva'] = ($detail['base_ht'] * $detail['taux']) / 100;
            }

            $totalTva = array_sum(array_column($parTauxTva, 'montant_tva'));
        }

        return [
            'total_ht' => round($totalHt, 2),
            'total_tva' => round($totalTva, 2),
            'total_ttc' => round($totalHt + $totalTva, 2),
            'par_taux_tva' => array_map(function ($detail) {
                return [
                    'taux' => round($detail['taux'], 2),
                    'base_ht' => round($detail['base_ht'], 2),
                    'montant_tva' => round($detail['montant_tva'], 2),
                ];
            }, array_values($parTauxTva)),
        ];
    }

    /**
     * Applique une remise globale au document.
     */
    public function applyRemiseGlobale(CommercialDocument $document, float $pct = 0, float $montant = 0): void
    {
        $document->update([
            'remise_globale_pct' => $pct,
            'remise_globale_montant' => $montant,
        ]);

        $this->recalculateDocument($document);
    }

    /**
     * Recalcule et met à jour les totaux du document.
     */
    public function recalculateDocument(CommercialDocument $document): void
    {
        $totaux = $this->calculateDocumentTotals($document);

        $document->updateQuietly([
            'total_ht' => $totaux['total_ht'],
            'total_tva' => $totaux['total_tva'],
            'total_ttc' => $totaux['total_ttc'],
        ]);
    }

    /**
     * Calcule le montant restant à payer pour une facture.
     */
    public function calculateSolde(CommercialDocument $facture): float
    {
        if (! $facture->isFacture()) {
            return 0.0;
        }

        $totalPaiements = (float) $facture->paiements()->sum('montant');

        return round((float) $facture->total_ttc - $totalPaiements, 2);
    }
}
