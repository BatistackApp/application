<?php

namespace Database\Factories\Commerce;

use App\Enums\Commerce\ModePaiement;
use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\Paiement;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaiementFactory extends Factory
{
    protected $model = Paiement::class;

    public function definition(): array
    {
        return [
            'facture_id' => CommercialDocument::factory()->facture(),
            'date_paiement' => fake()->dateTimeBetween('-3 months', 'now'),
            'montant' => fake()->randomFloat(2, 100, 10000),
            'mode_paiement' => fake()->randomElement(ModePaiement::cases()),
            'reference_paiement' => fake()->optional()->regexify('[A-Z0-9]{10}'),
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function virement(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_paiement' => ModePaiement::VIREMENT,
            'reference_paiement' => 'VIR'.fake()->numerify('########'),
        ]);
    }

    public function cheque(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_paiement' => ModePaiement::CHEQUE,
            'reference_paiement' => 'CHQ'.fake()->numerify('########'),
        ]);
    }

    public function cb(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_paiement' => ModePaiement::CB,
            'reference_paiement' => 'CB'.fake()->numerify('########'),
        ]);
    }
}
