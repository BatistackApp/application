<?php

namespace App\Services\Compta;

use App\Enums\Compta\CompteSens;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;

class TvaCalculatorService
{
    /**
     * Calcule la TVA collectée pour une période donnée.
     */
    public function getTvaCollectee(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        // Compte 445710 - TVA collectée
        $compteTva = PlanComptable::where('numero', '445710')->first();

        if (! $compteTva) {
            return [
                'base_20' => 0,
                'montant_20' => 0,
                'base_10' => 0,
                'montant_10' => 0,
                'base_55' => 0,
                'montant_55' => 0,
                'total' => 0,
            ];
        }

        $totalTvaCollectee = LigneEcriture::whereHas('ecriture', function ($query) use ($debut, $fin) {
            $query->valide()->periode($debut, $fin);
        })
            ->where('compte_id', $compteTva->id)
            ->where('sens', CompteSens::CREDIT)
            ->sum('montant');

        // Calcul bases HT à partir des produits (simplifié - à affiner selon besoin)
        $compteProduit = PlanComptable::where('numero', 'like', '70%')->get()->pluck('id');

        $baseHt = LigneEcriture::whereHas('ecriture', function ($query) use ($debut, $fin) {
            $query->valide()->periode($debut, $fin);
        })
            ->whereIn('compte_id', $compteProduit)
            ->where('sens', CompteSens::CREDIT)
            ->sum('montant');

        // Répartition par taux (simplifié - assume majoritairement 20%)
        return [
            'base_20' => round($baseHt, 2),
            'montant_20' => round($totalTvaCollectee, 2),
            'base_10' => 0,
            'montant_10' => 0,
            'base_55' => 0,
            'montant_55' => 0,
            'total' => round($totalTvaCollectee, 2),
        ];
    }

    /**
     * Calcule la TVA déductible pour une période donnée.
     */
    public function getTvaDeductible(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        // Compte 445620 - TVA déductible sur immobilisations
        $compteTvaImmo = PlanComptable::where('numero', '445620')->first();

        // Compte 445660 - TVA déductible sur biens et services
        $compteTvaBiens = PlanComptable::where('numero', '445660')->first();

        $tvaImmo = $compteTvaImmo
            ? LigneEcriture::whereHas('ecriture', function ($query) use ($debut, $fin) {
                $query->valide()->periode($debut, $fin);
            })
                ->where('compte_id', $compteTvaImmo->id)
                ->where('sens', CompteSens::DEBIT)
                ->sum('montant')
            : 0;

        $tvaBiens = $compteTvaBiens
            ? LigneEcriture::whereHas('ecriture', function ($query) use ($debut, $fin) {
                $query->valide()->periode($debut, $fin);
            })
                ->where('compte_id', $compteTvaBiens->id)
                ->where('sens', CompteSens::DEBIT)
                ->sum('montant')
            : 0;

        return [
            'immobilisations' => round($tvaImmo, 2),
            'biens_services' => round($tvaBiens, 2),
            'total' => round($tvaImmo + $tvaBiens, 2),
        ];
    }

    /**
     * Calcule le solde de TVA (à payer ou crédit).
     */
    public function getSoldeTva(\DateTimeInterface $debut, \DateTimeInterface $fin): float
    {
        $collectee = $this->getTvaCollectee($debut, $fin);
        $deductible = $this->getTvaDeductible($debut, $fin);

        return round($collectee['total'] - $deductible['total'], 2);
    }
}
