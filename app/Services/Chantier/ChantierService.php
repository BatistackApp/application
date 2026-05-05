<?php

namespace App\Services\Chantier;

use App\Enums\Chantier\ChantierStatus;
use App\Jobs\Chantier\GeocodeChantierJob;
use App\Models\Chantier\Chantier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChantierService
{
    /**
     * Crée un nouveau chantier en statut DRAFT.
     */
    public function create(array $data, User $user): Chantier
    {
        return DB::transaction(function () use ($data, $user) {
            $chantier = Chantier::create([
                ...$data,
                'reference' => $this->generateReference(),
                'status' => ChantierStatus::DRAFT,
                'responsable_id' => $data['responsable_id'] ?? $user->id,
            ]);

            if (! empty($chantier->adresse) && ! empty($chantier->ville)) {
                GeocodeChantierJob::dispatch($chantier);
            }

            return $chantier;
        });
    }

    /**
     * Met à jour un chantier et re-géocode si l'adresse a changé.
     */
    public function update(Chantier $chantier, array $data): Chantier
    {
        return DB::transaction(function () use ($chantier, $data) {
            $adresseChanged = ($data['adresse'] ?? null) !== $chantier->adresse
                || ($data['ville'] ?? null) !== $chantier->ville;

            $chantier->update($data);

            if ($adresseChanged && ! empty($chantier->adresse)) {
                GeocodeChantierJob::dispatch($chantier->fresh());
            }

            return $chantier->fresh();
        });
    }

    /**
     * Transitions de statut — toutes les règles métier passent ici.
     */
    public function transitionTo(Chantier $chantier, ChantierStatus $newStatus): Chantier
    {
        if (! $chantier->status->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Transition impossible : {$chantier->status->getLabel()} → {$newStatus->getLabel()}.",
            ]);
        }

        $data = ['status' => $newStatus];

        // Dates automatiques selon la transition
        if ($newStatus === ChantierStatus::ACTIVE && $chantier->date_debut_reelle === null) {
            $data['date_debut_reelle'] = now();
        }

        if ($newStatus === ChantierStatus::CLOSED && $chantier->date_fin_reelle === null) {
            $data['date_fin_reelle'] = now();
        }

        $chantier->update($data);

        return $chantier->fresh();
    }

    /**
     * Raccourcis sémantiques pour les transitions courantes.
     */
    public function open(Chantier $chantier): Chantier
    {
        return $this->transitionTo($chantier, ChantierStatus::OPEN);
    }

    public function activate(Chantier $chantier): Chantier
    {
        return $this->transitionTo($chantier, ChantierStatus::ACTIVE);
    }

    public function pause(Chantier $chantier): Chantier
    {
        return $this->transitionTo($chantier, ChantierStatus::PAUSED);
    }

    public function close(Chantier $chantier): Chantier
    {
        return $this->transitionTo($chantier, ChantierStatus::CLOSED);
    }

    public function archive(Chantier $chantier): Chantier
    {
        return $this->transitionTo($chantier, ChantierStatus::ARCHIVED);
    }

    /**
     * Génère une référence unique de type CH-2026-001.
     */
    public function generateReference(): string
    {
        $year = now()->year;
        $count = Chantier::withTrashed()
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('CH-%d-%03d', $year, $count);
    }
}
