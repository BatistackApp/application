<?php

namespace Database\Factories\Compta;

use App\Enums\Compta\CompteSens;
use App\Models\Chantier\Chantier;
use App\Models\Compta\Ecriture;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;
use Illuminate\Database\Eloquent\Factories\Factory;

class LigneEcritureFactory extends Factory
{
    protected $model = LigneEcriture::class;

    public function definition(): array
    {
        return [
            'ecriture_id' => Ecriture::factory(),
            'compte_id' => PlanComptable::factory(),
            'sens' => $this->faker->randomElement(CompteSens::cases()),
            'montant' => $this->faker->randomFloat(2, 10, 10000),
            'libelle' => $this->faker->sentence(),
            'ordre' => 0,
        ];
    }

    public function debit(?float $montant = null): static
    {
        return $this->state(fn (array $attributes) => [
            'sens' => CompteSens::DEBIT,
            'montant' => $montant ?? $this->faker->randomFloat(2, 10, 10000),
        ]);
    }

    public function credit(?float $montant = null): static
    {
        return $this->state(fn (array $attributes) => [
            'sens' => CompteSens::CREDIT,
            'montant' => $montant ?? $this->faker->randomFloat(2, 10, 10000),
        ]);
    }

    public function withCompte(PlanComptable $compte): static
    {
        return $this->state(fn (array $attributes) => [
            'compte_id' => $compte->id,
        ]);
    }

    public function withChantier(?Chantier $chantier = null): static
    {
        return $this->state(fn (array $attributes) => [
            'chantier_id' => $chantier?->id ?? Chantier::factory(),
        ]);
    }

    public function lettree(): static
    {
        return $this->state(fn (array $attributes) => [
            'lettrage' => strtoupper($this->faker->bothify('??###')),
            'date_lettrage' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
