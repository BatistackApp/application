<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\Tiers;
use App\Models\Tiers\TiersContact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TiersContactFactory extends Factory
{
    protected $model = TiersContact::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'fonction' => $this->faker->jobTitle(),
            'tel_fix' => $this->faker->e164PhoneNumber(),
            'tel_portable' => $this->faker->e164PhoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'dgcp_concilent' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
