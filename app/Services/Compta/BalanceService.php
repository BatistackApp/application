<?php

namespace App\Services\Compta;

use App\Enums\Compta\CompteSens;
use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\PlanComptable;
use Illuminate\Support\Collection;

class BalanceService
{
    public function __construct(
        protected GrandLivreService $grandLivreService
    ) {}

    /**
     * Génère la balance générale.
     */
    public function genererBalanceGenerale(
        ExerciceComptable $exercice,
        ?\DateTimeInterface $dateDebut = null,
        ?\DateTimeInterface $dateFin = null
    ): Collection {
        $grandLivre = $this->grandLivreService->generer($exercice, $dateDebut, $dateFin);

        return $grandLivre->map(function ($ligne) {
            return [
                'numero' => $ligne['compte']->numero,
                'libelle' => $ligne['compte']->libelle,
                'type' => $ligne['compte']->type,
                'total_debit' => round($ligne['total_debit'], 2),
                'total_credit' => round($ligne['total_credit'], 2),
                'solde_debit' => $ligne['sens_solde'] === CompteSens::DEBIT ? abs($ligne['solde']) : 0,
                'solde_credit' => $ligne['sens_solde'] === CompteSens::CREDIT ? abs($ligne['solde']) : 0,
            ];
        })->sortBy('numero')->values();
    }

    /**
     * Génère la balance auxiliaire (clients ou fournisseurs).
     */
    public function genererBalanceAuxiliaire(
        ExerciceComptable $exercice,
        string $typeCompte = '411' // 411 = clients, 401 = fournisseurs
    ): Collection {
        $comptes = PlanComptable::where('numero', 'like', "{$typeCompte}%")
            ->orderBy('numero')
            ->get();

        return $comptes->map(function ($compte) use ($exercice) {
            $solde = $this->grandLivreService->getSoldeCompteAuDate($compte, $exercice->date_fin);

            // Ne retourner que les comptes avec un solde non nul
            if ($solde == 0) {
                return null;
            }

            return [
                'compte' => $compte,
                'numero' => $compte->numero,
                'libelle' => $compte->libelle,
                'solde' => abs($solde),
                'sens' => $solde >= 0 ? CompteSens::DEBIT : CompteSens::CREDIT,
            ];
        })->filter()->values();
    }

    /**
     * Vérifie l'équilibre de la balance.
     */
    public function verifierEquilibre(Collection $balance): array
    {
        $totalDebit = $balance->sum('total_debit');
        $totalCredit = $balance->sum('total_credit');
        $totalSoldeDebit = $balance->sum('solde_debit');
        $totalSoldeCredit = $balance->sum('solde_credit');

        $equilibree = round($totalDebit, 2) === round($totalCredit, 2)
            && round($totalSoldeDebit, 2) === round($totalSoldeCredit, 2);

        return [
            'equilibree' => $equilibree,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'total_solde_debit' => round($totalSoldeDebit, 2),
            'total_solde_credit' => round($totalSoldeCredit, 2),
            'ecart_mouvements' => round(abs($totalDebit - $totalCredit), 2),
            'ecart_soldes' => round(abs($totalSoldeDebit - $totalSoldeCredit), 2),
        ];
    }
}
