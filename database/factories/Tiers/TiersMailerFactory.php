<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\Tiers;
use App\Models\Tiers\TiersMailer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TiersMailerFactory extends Factory
{
    protected $model = TiersMailer::class;

    public function definition(): array
    {
        return [
            'subject' => $this->faker->word(),
            'content' => $this->faker->word(),
            'status' => $this->faker->word(),
            'published_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tiers_contact_id' => Tiers::factory(),
        ];
    }
}
