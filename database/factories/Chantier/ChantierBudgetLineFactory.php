<?php

namespace Database\Factories\Chantier;

use App\Enums\Chantier\ChantierBudgetType;
use App\Models\Chantier\Chantier;
use App\Models\Chantier\ChantierBudgetLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChantierBudgetLine>
 */
class ChantierBudgetLineFactory extends Factory
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
            'article_id' => null,
            'type' => $this->faker->randomElement(ChantierBudgetType::cases()),
            'designation' => $this->faker->words(3, true),
            'quantite' => $this->faker->randomFloat(2, 1, 100),
            'unite' => $this->faker->randomElement(['h', 'u', 'm²', 'm³', 'kg', 'forfait']),
            'cout_unitaire' => $this->faker->randomFloat(2, 10, 500),
            'note' => null,
        ];
    }
}
