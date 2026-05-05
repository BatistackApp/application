<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\Tiers;
use App\Models\Tiers\TiersSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

class TiersSettingsFactory extends Factory
{
    protected $model = TiersSettings::class;

    public function definition(): array
    {
        return [
            'outstanding' => $this->faker->randomFloat(),
            'followup' => $this->faker->boolean(),
            'followup_terms' => $this->faker->words(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
