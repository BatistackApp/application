<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Chantier\Chantier;
use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\CommercialDocumentLine;
use App\Models\Tiers\Tiers;
use App\Services\Chantier\ChantierBudgetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommercialDocumentService
{
    public function __construct(
        protected CommercialCalculator $calculator
    ) {}

    /**
     * Crée un nouveau document commercial.
     * @throws \Throwable
     */
    public function create(
        DocumentType $type,
        Tiers $client,
        array $data = []
    ): CommercialDocument {
        return DB::transaction(function () use ($type, $client, $data) {
            $document = CommercialDocument::create([
                'type' => $type,
                'reference' => $this->generateReference($type),
                'status' => DocumentStatus::DRAFT,
                'client_id' => $client->id,
                'chantier_id' => $data['chantier_id'] ?? null,
                'date_document' => $data['date_document'] ?? now(),
                'date_validite' => $data['date_validite'] ?? null,
                'date_echeance' => $data['date_echeance'] ?? null,
                'notes' => $data['notes'] ?? null,
                'conditions_reglement' => $data['conditions_reglement'] ?? 'Paiement à 30 jours fin de mois',
                'avancement_pct' => $data['avancement_pct'] ?? null,
            ]);

            // Si facture, calculer échéance par défaut
            if (in_array($type, [DocumentType::FACTURE, DocumentType::FACTURE_ACOMPTE])) {
                if (! $document->date_echeance) {
                    $document->date_echeance = now()->addDays(30);
                    $document->save();
                }
            }

            // Si devis, calculer validité par défaut
            if ($type === DocumentType::DEVIS && ! $document->date_validite) {
                $document->date_validite = now()->addDays(30);
                $document->save();
            }

            return $document;
        });
    }

    /**
     * Génère une référence unique pour le document.
     */
    public function generateReference(DocumentType $type): string
    {
        $year = now()->year;
        $prefix = $type->getPrefix();

        $lastDocument = CommercialDocument::where('type', $type)
            ->where('reference', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('reference')
            ->first();

        $sequence = 1;
        if ($lastDocument) {
            preg_match('/-(\d+)$/', $lastDocument->reference, $matches);
            $sequence = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        }

        return sprintf('%s-%d-%03d', $prefix, $year, $sequence);
    }

    /**
     * Ajoute une ligne au document.
     */
    public function addLine(CommercialDocument $document, array $data): CommercialDocumentLine
    {
        $line = $document->lines()->create([
            'article_id' => $data['article_id'] ?? null,
            'ouvrage_id' => $data['ouvrage_id'] ?? null,
            'designation' => $data['designation'],
            'quantite' => $data['quantite'],
            'unite' => $data['unite'] ?? null,
            'prix_unitaire_ht' => $data['prix_unitaire_ht'],
            'taux_tva' => $data['taux_tva'] ?? 20,
            'remise_pct' => $data['remise_pct'] ?? 0,
            'remise_montant' => $data['remise_montant'] ?? 0,
            'ordre' => $data['ordre'] ?? $document->lines()->max('ordre') + 1,
        ]);

        $this->calculator->recalculateDocument($document->fresh());

        return $line;
    }

    /**
     * Convertit un document vers un autre type.
     * @throws \Throwable
     */
    public function convert(CommercialDocument $source, DocumentType $targetType): CommercialDocument
    {
        // Vérifier que la conversion est autorisée
        if (! in_array($source->type, $targetType->acceptsConversionFrom())) {
            throw ValidationException::withMessages([
                'type' => "Impossible de convertir un {$source->type->getLabel()} en {$targetType->getLabel()}.",
            ]);
        }

        return DB::transaction(function () use ($source, $targetType) {
            // Créer le nouveau document
            $target = $this->create($targetType, $source->client, [
                'chantier_id' => $source->chantier_id,
                'date_document' => now(),
                'notes' => $source->notes,
                'conditions_reglement' => $source->conditions_reglement,
            ]);

            $target->update(['parent_document_id' => $source->id]);

            // Copier les lignes
            foreach ($source->lines as $sourceLine) {
                $this->addLine($target, [
                    'article_id' => $sourceLine->article_id,
                    'ouvrage_id' => $sourceLine->ouvrage_id,
                    'designation' => $sourceLine->designation,
                    'quantite' => $sourceLine->quantite,
                    'unite' => $sourceLine->unite,
                    'prix_unitaire_ht' => $sourceLine->prix_unitaire_ht,
                    'taux_tva' => $sourceLine->taux_tva,
                    'remise_pct' => $sourceLine->remise_pct,
                    'remise_montant' => $sourceLine->remise_montant,
                    'ordre' => $sourceLine->ordre,
                ]);
            }

            // Copier remise globale si présente
            if ($source->remise_globale_pct > 0 || $source->remise_globale_montant > 0) {
                $this->calculator->applyRemiseGlobale(
                    $target,
                    $source->remise_globale_pct,
                    $source->remise_globale_montant
                );
            }

            return $target->fresh();
        });
    }

    /**
     * Génère une facture par avancement de chantier.
     * @throws \Throwable
     */
    public function createFactureAvancement(
        Chantier $chantier,
        float $avancementPct,
        bool $isAcompte = false
    ): CommercialDocument {
        if (! $chantier->client) {
            throw ValidationException::withMessages([
                'chantier' => 'Le chantier doit avoir un client associé.',
            ]);
        }

        $budgetService = app(ChantierBudgetService::class);
        $budgetTotal = $budgetService->getBudgetTotal($chantier);

        $montant = ($budgetTotal * $avancementPct) / 100;

        $type = $isAcompte ? DocumentType::FACTURE_ACOMPTE : DocumentType::FACTURE;

        $facture = $this->create($type, $chantier->client, [
            'chantier_id' => $chantier->id,
            'avancement_pct' => $avancementPct,
            'notes' => "Facturation à {$avancementPct}% d'avancement du chantier {$chantier->reference}",
        ]);

        // Créer une ligne unique pour l'avancement
        $this->addLine($facture, [
            'designation' => "Avancement {$avancementPct}% - {$chantier->nom}",
            'quantite' => 1,
            'unite' => 'forfait',
            'prix_unitaire_ht' => $montant,
            'taux_tva' => 20,
        ]);

        return $facture->fresh();
    }

    /**
     * Valide un document (passage de DRAFT à SENT).
     */
    public function validate(CommercialDocument $document): CommercialDocument
    {
        if ($document->status !== DocumentStatus::DRAFT) {
            throw ValidationException::withMessages([
                'status' => 'Seuls les documents en brouillon peuvent être validés.',
            ]);
        }

        if ($document->lines()->count() === 0) {
            throw ValidationException::withMessages([
                'lines' => 'Le document doit avoir au moins une ligne.',
            ]);
        }

        $document->update(['status' => DocumentStatus::SENT]);

        return $document->fresh();
    }

    /**
     * Accepte un document (devis, BDC).
     */
    public function accept(CommercialDocument $document): CommercialDocument
    {
        if ($document->status !== DocumentStatus::SENT) {
            throw ValidationException::withMessages([
                'status' => 'Seuls les documents envoyés peuvent être acceptés.',
            ]);
        }

        $document->update(['status' => DocumentStatus::ACCEPTED]);

        return $document->fresh();
    }

    /**
     * Refuse un document (devis).
     */
    public function refuse(CommercialDocument $document, ?string $motif = null): CommercialDocument
    {
        if ($document->status !== DocumentStatus::SENT) {
            throw ValidationException::withMessages([
                'status' => 'Seuls les documents envoyés peuvent être refusés.',
            ]);
        }

        $notes = $document->notes;
        if ($motif) {
            $notes .= "\n\nMotif de refus : {$motif}";
        }

        $document->update([
            'status' => DocumentStatus::REFUSED,
            'notes' => $notes,
        ]);

        return $document->fresh();
    }

    /**
     * Marque un bon de livraison comme livré.
     */
    public function markAsDelivered(CommercialDocument $document): CommercialDocument
    {
        if ($document->type !== DocumentType::BON_LIVRAISON) {
            throw ValidationException::withMessages([
                'type' => 'Seuls les bons de livraison peuvent être marqués comme livrés.',
            ]);
        }

        $document->update(['status' => DocumentStatus::DELIVERED]);

        return $document->fresh();
    }

    /**
     * Annule un document.
     */
    public function cancel(CommercialDocument $document): CommercialDocument
    {
        $document->update(['status' => DocumentStatus::CANCELLED]);

        return $document->fresh();
    }
}
