<?php

namespace Database\Factories\Compta;

use App\Enums\Compta\CompteType;
use App\Models\Compta\PlanComptable;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanComptableFactory extends Factory
{
    protected $model = PlanComptable::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(CompteType::cases());

        return [
            'numero' => $this->generateNumero($type),
            'libelle' => $this->faker->words(3, true),
            'type' => $type,
            'actif' => true,
            'lettrable' => false,
            'analytique' => false,
        ];
    }

    protected function generateNumero(CompteType $type): string
    {
        $classe = $type->value;
        $sousCompte = $this->faker->numberBetween(10, 99);
        $detail = $this->faker->numberBetween(100, 999);

        return "{$classe}{$sousCompte}{$detail}";
    }

    public function classe(int $classe): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => $classe.str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'type' => CompteType::from((string) $classe),
        ]);
    }

    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => '411'.str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'type' => CompteType::CLASSE_4,
            'lettrable' => true,
        ]);
    }

    public function fournisseur(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => '401'.str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'type' => CompteType::CLASSE_4,
            'lettrable' => true,
        ]);
    }

    public function tvaCollectee(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => '445710',
            'libelle' => 'TVA collectée',
            'type' => CompteType::CLASSE_4,
        ]);
    }

    public function tvaDeductible(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => '445660',
            'libelle' => 'TVA déductible sur biens et services',
            'type' => CompteType::CLASSE_4,
        ]);
    }

    public function banque(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => '512000',
            'libelle' => 'Banque',
            'type' => CompteType::CLASSE_5,
        ]);
    }

    public function charge(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => '6'.str_pad($this->faker->unique()->numberBetween(1, 9999), 5, '0', STR_PAD_LEFT),
            'type' => CompteType::CLASSE_6,
            'analytique' => true,
        ]);
    }

    public function produit(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero' => '7'.str_pad($this->faker->unique()->numberBetween(1, 9999), 5, '0', STR_PAD_LEFT),
            'type' => CompteType::CLASSE_7,
            'analytique' => true,
        ]);
    }
}
