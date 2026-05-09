<?php

namespace App\Observers\Compta;

use App\Enums\Compta\EcritureStatus;
use App\Models\Compta\Ecriture;
use Log;

class EcritureObserver
{
    public function updating(Ecriture $ecriture): bool
    {
        if ($ecriture->getOriginal('status') === EcritureStatus::VALIDE->value) {
            // Une écriture validée ne peut plus être modifiée
            if ($ecriture->isDirty() && ! $ecriture->isDirty('extourne_ecriture_id')) {
                throw new \RuntimeException(
                    "Écriture {$ecriture->numero_piece} validée : modification interdite. "
                    ."Utilisez l'extourne pour corriger."
                );
            }
        }

        return true;
    }

    public function updated(Ecriture $ecriture): void
    {
        if ($ecriture->wasChanged('status') && $ecriture->status === EcritureStatus::VALIDE) {
            Log::info("Écriture {$ecriture->numero_piece} validée par user #{$ecriture->validated_by}");
        }
    }

    public function saving(Ecriture $ecriture): void
    {
        // Si l'écriture est validée, vérifier l'équilibre
        if ($ecriture->status === EcritureStatus::VALIDE && $ecriture->isDirty('status')) {
            if (! $ecriture->isEquilibree()) {
                throw new \RuntimeException(
                    "Écriture {$ecriture->numero_piece} déséquilibrée : "
                    ."Débit = {$ecriture->total_debit} € / Crédit = {$ecriture->total_credit} €"
                );
            }

            if ($ecriture->lignes()->count() < 2) {
                throw new \RuntimeException(
                    "Écriture {$ecriture->numero_piece} doit avoir au moins 2 lignes."
                );
            }
        }
    }

    public function deleting(Ecriture $ecriture): bool
    {
        if ($ecriture->status === EcritureStatus::VALIDE) {
            throw new \RuntimeException(
                "Écriture {$ecriture->numero_piece} validée : suppression interdite. "
                ."Utilisez l'extourne pour annuler."
            );
        }

        return true;
    }
}
