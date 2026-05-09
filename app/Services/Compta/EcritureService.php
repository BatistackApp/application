<?php

namespace App\Services\Compta;

use App\Enums\Compta\CompteSens;
use App\Enums\Compta\EcritureStatus;
use App\Models\Compta\Ecriture;
use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\Journal;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EcritureService
{
    /**
     * Crée une nouvelle écriture comptable.
     *
     * @throws \Throwable
     */
    public function create(
        Journal $journal,
        \DateTimeInterface $date,
        string $libelle,
        array $lignes,
        $source = null
    ): Ecriture {
        return DB::transaction(function () use ($journal, $date, $libelle, $lignes, $source) {
            // Trouver l'exercice correspondant
            $exercice = $this->findExercice($date);

            if (! $exercice) {
                throw ValidationException::withMessages([
                    'date' => "Aucun exercice comptable actif pour la date {$date->format('d/m/Y')}.",
                ]);
            }

            if (! $exercice->canBeModified()) {
                throw ValidationException::withMessages([
                    'exercice' => 'Exercice clôturé : écriture interdite.',
                ]);
            }

            // Créer l'écriture
            $ecriture = Ecriture::create([
                'exercice_comptable_id' => $exercice->id,
                'journal_id' => $journal->id,
                'numero_piece' => $this->generateNumeroPiece($journal, $date),
                'date_ecriture' => $date,
                'libelle' => $libelle,
                'status' => EcritureStatus::BROUILLON,
                'source_type' => $source ? get_class($source) : null,
                'source_id' => $source?->id,
                'created_by' => auth()->id(),
            ]);

            // Ajouter les lignes
            foreach ($lignes as $index => $ligne) {
                $this->addLigne($ecriture, $ligne, $index + 1);
            }

            return $ecriture->fresh();
        });
    }

    /**
     * Ajoute une ligne à une écriture.
     */
    public function addLigne(Ecriture $ecriture, array $data, int $ordre = 0): LigneEcriture
    {
        if (! $ecriture->canBeModified()) {
            throw ValidationException::withMessages([
                'ecriture' => 'Cette écriture ne peut plus être modifiée.',
            ]);
        }

        $compte = is_int($data['compte_id'] ?? null)
            ? PlanComptable::findOrFail($data['compte_id'])
            : PlanComptable::where('numero', $data['compte_numero'] ?? '')->firstOrFail();

        return $ecriture->lignes()->create([
            'compte_id' => $compte->id,
            'sens' => CompteSens::from($data['sens']),
            'montant' => $data['montant'],
            'libelle' => $data['libelle'] ?? $ecriture->libelle,
            'chantier_id' => $data['chantier_id'] ?? null,
            'ordre' => $ordre ?: ($ecriture->lignes()->max('ordre') + 1),
        ]);
    }

    /**
     * Valide une écriture (BROUILLON → VALIDE).
     */
    public function valider(Ecriture $ecriture): Ecriture
    {
        if (! $ecriture->canBeValidated()) {
            throw ValidationException::withMessages([
                'ecriture' => 'Écriture non validable : vérifiez l\'équilibre et le nombre de lignes.',
            ]);
        }

        $ecriture->update([
            'status' => EcritureStatus::VALIDE,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        return $ecriture->fresh();
    }

    /**
     * Extourne une écriture validée (crée l'écriture inverse).
     * @throws \Throwable
     */
    public function extourner(Ecriture $ecriture, \DateTimeInterface $dateExtourne): Ecriture
    {
        if ($ecriture->status !== EcritureStatus::VALIDE) {
            throw ValidationException::withMessages([
                'ecriture' => 'Seules les écritures validées peuvent être extournées.',
            ]);
        }

        return DB::transaction(function () use ($ecriture, $dateExtourne) {
            // Créer l'écriture d'extourne avec lignes inversées
            $lignesExtourne = $ecriture->lignes->map(function ($ligne) {
                return [
                    'compte_id' => $ligne->compte_id,
                    'sens' => $ligne->sens->opposite()->value,
                    'montant' => $ligne->montant,
                    'libelle' => 'Extourne : '.$ligne->libelle,
                    'chantier_id' => $ligne->chantier_id,
                ];
            })->toArray();

            $ecritureExtourne = $this->create(
                $ecriture->journal,
                $dateExtourne,
                'Extourne : '.$ecriture->libelle,
                $lignesExtourne,
                $ecriture->source
            );

            // Valider automatiquement l'extourne
            $this->valider($ecritureExtourne);

            // Marquer l'écriture originale comme extournée
            $ecriture->update([
                'status' => EcritureStatus::EXTOURNE,
                'extourne_ecriture_id' => $ecritureExtourne->id,
            ]);

            return $ecritureExtourne;
        });
    }

    /**
     * Génère un numéro de pièce unique.
     */
    protected function generateNumeroPiece(Journal $journal, \DateTimeInterface $date): string
    {
        $year = $date->format('Y');
        $month = $date->format('m');
        $prefix = "{$journal->code}-{$year}{$month}";

        $lastEcriture = Ecriture::where('numero_piece', 'like', "{$prefix}-%")
            ->orderByDesc('numero_piece')
            ->first();

        $sequence = 1;
        if ($lastEcriture) {
            preg_match('/-(\d+)$/', $lastEcriture->numero_piece, $matches);
            $sequence = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        }

        return sprintf('%s-%04d', $prefix, $sequence);
    }

    /**
     * Trouve l'exercice comptable correspondant à une date.
     */
    protected function findExercice(\DateTimeInterface $date): ?ExerciceComptable
    {
        return ExerciceComptable::where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->where('cloture', false)
            ->first();
    }

    /**
     * Supprime une écriture (brouillon uniquement).
     */
    public function delete(Ecriture $ecriture): void
    {
        if (! $ecriture->canBeModified()) {
            throw ValidationException::withMessages([
                'ecriture' => 'Cette écriture ne peut pas être supprimée.',
            ]);
        }

        $ecriture->delete();
    }
}
