<?php

namespace App\Services\Compta;

use App\Enums\Compta\CompteSens;
use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GrandLivreService
{
    /**
     * Génère le grand livre pour un exercice.
     */
    public function generer(
        ExerciceComptable $exercice,
        ?\DateTimeInterface $dateDebut = null,
        ?\DateTimeInterface $dateFin = null
    ): Collection {
        $dateDebut = $dateDebut ?? $exercice->date_debut;
        $dateFin = $dateFin ?? $exercice->date_fin;

        // Récupérer tous les comptes ayant des mouvements
        $comptesAvecMouvements = LigneEcriture::query()
            ->select('compte_id')
            ->whereHas('ecriture', function ($query) use ($exercice, $dateDebut, $dateFin) {
                $query->where('exercice_comptable_id', $exercice->id)
                    ->valide()
                    ->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
            })
            ->distinct()
            ->pluck('compte_id');

        $comptes = PlanComptable::whereIn('id', $comptesAvecMouvements)
            ->orderBy('numero')
            ->get();

        return $comptes->map(function ($compte) use ($exercice, $dateDebut, $dateFin) {
            $mouvements = $this->getMouvementsCompte($compte, $exercice, $dateDebut, $dateFin);
            $solde = $this->calculerSolde($compte, $mouvements);

            return [
                'compte' => $compte,
                'mouvements' => $mouvements,
                'total_debit' => $mouvements->where('sens', CompteSens::DEBIT)->sum('montant'),
                'total_credit' => $mouvements->where('sens', CompteSens::CREDIT)->sum('montant'),
                'solde' => $solde,
                'sens_solde' => $this->getSensSolde($compte, $solde),
            ];
        });
    }

    /**
     * Récupère les mouvements d'un compte.
     */
    protected function getMouvementsCompte(
        PlanComptable $compte,
        ExerciceComptable $exercice,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin
    ): Collection {
        return LigneEcriture::with(['ecriture.journal'])
            ->whereHas('ecriture', function ($query) use ($exercice, $dateDebut, $dateFin) {
                $query->where('exercice_comptable_id', $exercice->id)
                    ->valide()
                    ->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
            })
            ->where('compte_id', $compte->id)
            ->orderBy(DB::raw('(SELECT date_ecriture FROM ecritures WHERE ecritures.id = lignes_ecriture.ecriture_id)'))
            ->get();
    }

    /**
     * Calcule le solde d'un compte.
     */
    public function calculerSolde(PlanComptable $compte, Collection $mouvements): float
    {
        $totalDebit = $mouvements->where('sens', CompteSens::DEBIT)->sum('montant');
        $totalCredit = $mouvements->where('sens', CompteSens::CREDIT)->sum('montant');

        // Sens naturel du compte
        $sensNaturel = $compte->type->getSensNaturel();

        if ($sensNaturel === CompteSens::DEBIT) {
            return round($totalDebit - $totalCredit, 2);
        } else {
            return round($totalCredit - $totalDebit, 2);
        }
    }

    /**
     * Détermine le sens du solde (débiteur ou créditeur).
     */
    protected function getSensSolde(PlanComptable $compte, float $solde): CompteSens
    {
        if ($solde >= 0) {
            return $compte->type->getSensNaturel();
        } else {
            return $compte->type->getSensNaturel()->opposite();
        }
    }

    /**
     * Récupère le solde d'un compte à une date donnée.
     */
    public function getSoldeCompteAuDate(
        PlanComptable $compte,
        \DateTimeInterface $date
    ): float {
        $exercice = ExerciceComptable::current()->first();

        if (! $exercice) {
            return 0;
        }

        $mouvements = $this->getMouvementsCompte(
            $compte,
            $exercice,
            $exercice->date_debut,
            $date
        );

        return $this->calculerSolde($compte, $mouvements);
    }
}
