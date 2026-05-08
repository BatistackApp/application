<?php

namespace App\Services\Commerce;

use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\Relance;
use App\Models\User;
use Illuminate\Support\Collection;

class RelanceService
{
    /**
     * Détecte les factures impayées.
     */
    public function detectImpayes(): Collection
    {
        return CommercialDocument::impayes()
            ->with(['client', 'paiements', 'relances'])
            ->get();
    }

    /**
     * Crée une relance pour une facture.
     */
    public function createRelance(
        CommercialDocument $facture,
        User $user,
        string $type,
        string $contenu
    ): Relance {
        if (! $facture->isFacture()) {
            throw new \InvalidArgumentException('Les relances concernent uniquement les factures.');
        }

        return $facture->relances()->create([
            'user_id' => $user->id,
            'date_relance' => now(),
            'type' => $type,
            'contenu' => $contenu,
        ]);
    }

    /**
     * Génère automatiquement des relances pour les impayés.
     */
    public function generateRelancesAuto(User $user): Collection
    {
        $impayes = $this->detectImpayes();
        $relancesCreated = collect();

        foreach ($impayes as $facture) {
            $joursRetard = now()->diffInDays($facture->date_echeance);

            // Éviter les doublons de relances
            $derniereRelance = $facture->relances()->latest('date_relance')->first();
            if ($derniereRelance && $derniereRelance->date_relance->isToday()) {
                continue;
            }

            // Relance selon le niveau de retard
            $contenu = match (true) {
                $joursRetard <= 7 => "Relance amiable - Échéance dépassée de {$joursRetard} jour(s).",
                $joursRetard <= 30 => "Relance de niveau 2 - Retard de paiement de {$joursRetard} jours.",
                default => "Mise en demeure - Retard de paiement important ({$joursRetard} jours).",
            };

            $relance = $this->createRelance($facture, $user, 'email', $contenu);
            $relancesCreated->push($relance);
        }

        return $relancesCreated;
    }

    /**
     * Calcule le nombre de jours de retard.
     */
    public function getJoursRetard(CommercialDocument $facture): int
    {
        if (! $facture->date_echeance || $facture->isFullyPaid()) {
            return 0;
        }

        return max(0, now()->diffInDays($facture->date_echeance, false));
    }
}
