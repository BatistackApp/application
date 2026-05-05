<?php

namespace Database\Factories\Chantier;

use App\Enums\Chantier\ChantierStatus;
use App\Models\Chantier\Chantier;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chantier>
 */
class ChantierFactory extends Factory
{
    public function definition(): array
    {
        $debut = $this->faker->dateTimeBetween('-6 months', '+1 month');
        $fin = $this->faker->dateTimeBetween($debut, '+8 months');
        $year = now()->year;

        return [
            'reference' => sprintf('CH-%d-%03d', $year, $this->faker->unique()->numberBetween(1, 999)),
            'nom' => $this->faker->words(3, true).' - Chantier',
            'description' => $this->faker->sentence(),
            'client_id' => Tiers::factory(),
            'responsable_id' => User::factory(),
            'status' => ChantierStatus::DRAFT,
            'adresse' => $this->faker->streetAddress(),
            'code_postal' => $this->faker->postcode(),
            'ville' => $this->faker->city(),
            'pays' => 'France',
            'date_debut_prevue' => $debut,
            'date_fin_prevue' => $fin,
            'date_debut_reelle' => null,
            'date_fin_reelle' => null,
            'notes' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ChantierStatus::DRAFT]);
    }

    public function open(): static
    {
        return $this->state(['status' => ChantierStatus::OPEN]);
    }

    public function active(): static
    {
        return $this->state([
            'status' => ChantierStatus::ACTIVE,
            'date_debut_reelle' => now()->subDays(10),
        ]);
    }

    public function paused(): static
    {
        return $this->state([
            'status' => ChantierStatus::PAUSED,
            'date_debut_reelle' => now()->subDays(30),
        ]);
    }

    public function closed(): static
    {
        return $this->state([
            'status' => ChantierStatus::CLOSED,
            'date_debut_reelle' => now()->subMonths(3),
            'date_fin_reelle' => now()->subDays(5),
        ]);
    }
}
