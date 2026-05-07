<?php

namespace Database\Factories\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Chantier\Chantier;
use App\Models\Commerce\CommercialDocument;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommercialDocumentFactory extends Factory
{
    protected $model = CommercialDocument::class;

    public function definition(): array
    {
        $type = fake()->randomElement(DocumentType::cases());
        $dateDocument = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'type' => $type,
            'reference' => $this->generateReference($type),
            'status' => DocumentStatus::DRAFT,
            'client_id' => Tiers::factory()->customer(),
            'chantier_id' => null,
            'date_document' => $dateDocument,
            'date_validite' => $type === DocumentType::DEVIS ?
                fake()->dateTimeBetween($dateDocument, '+30 days') : null,
            'date_echeance' => in_array($type, [DocumentType::FACTURE, DocumentType::FACTURE_ACOMPTE]) ?
                fake()->dateTimeBetween($dateDocument, '+60 days') : null,
            'total_ht' => 0,
            'total_tva' => 0,
            'total_ttc' => 0,
            'remise_globale_pct' => 0,
            'remise_globale_montant' => 0,
            'avancement_pct' => null,
            'notes' => fake()->optional()->sentence(),
            'conditions_reglement' => 'Paiement à 30 jours fin de mois',
            'parent_document_id' => null,
        ];
    }

    protected function generateReference(DocumentType $type): string
    {
        $year = now()->year;
        $prefix = $type->getPrefix();
        $sequence = fake()->numberBetween(1, 999);

        return sprintf('%s-%d-%03d', $prefix, $year, $sequence);
    }

    // ─── States ──────────────────────────────────────────────────────────────

    public function devis(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::DEVIS,
            'reference' => $this->generateReference(DocumentType::DEVIS),
            'date_validite' => fake()->dateTimeBetween($attributes['date_document'], '+30 days'),
        ]);
    }

    public function bonCommande(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::BON_COMMANDE,
            'reference' => $this->generateReference(DocumentType::BON_COMMANDE),
        ]);
    }

    public function bonLivraison(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::BON_LIVRAISON,
            'reference' => $this->generateReference(DocumentType::BON_LIVRAISON),
        ]);
    }

    public function facture(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::FACTURE,
            'reference' => $this->generateReference(DocumentType::FACTURE),
            'date_echeance' => fake()->dateTimeBetween($attributes['date_document'], '+60 days'),
        ]);
    }

    public function factureAcompte(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::FACTURE_ACOMPTE,
            'reference' => $this->generateReference(DocumentType::FACTURE_ACOMPTE),
            'date_echeance' => fake()->dateTimeBetween($attributes['date_document'], '+60 days'),
            'avancement_pct' => fake()->randomFloat(2, 10, 50),
        ]);
    }

    public function avoir(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::AVOIR,
            'reference' => $this->generateReference(DocumentType::AVOIR),
        ]);
    }

    public function withChantier(): static
    {
        return $this->state(fn (array $attributes) => [
            'chantier_id' => Chantier::factory()->active(),
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::SENT,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::ACCEPTED,
        ]);
    }

    public function refused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::REFUSED,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::DELIVERED,
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::PARTIALLY_PAID,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::PAID,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::CANCELLED,
        ]);
    }

    public function withRemiseGlobale(): static
    {
        return $this->state(fn (array $attributes) => [
            'remise_globale_pct' => fake()->randomFloat(2, 5, 15),
        ]);
    }
}
