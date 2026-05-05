<?php

namespace Database\Factories\Chantier;

use App\Enums\Chantier\ChantierCoutType;
use App\Models\Chantier\Chantier;
use App\Models\Chantier\ChantierCout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChantierCout>
 */
class ChantierCoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chantier_id' => Chantier::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(ChantierCoutType::cases()),
            'designation' => $this->faker->words(3, true),
            'montant_ht' => $this->faker->randomFloat(2, 50, 5000),
            'date_imputation' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'source_type' => null,
            'source_id' => null,
            'note' => null,
        ];
    }
}
