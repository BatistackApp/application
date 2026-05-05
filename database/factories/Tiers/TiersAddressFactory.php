<?php

namespace Database\Factories\Tiers;

use App\Enums\Tiers\TiersAddressType;
use App\Models\Tiers\Tiers;
use App\Models\Tiers\TiersAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class TiersAddressFactory extends Factory
{
    protected $model = TiersAddress::class;

    public function definition(): array
    {
        return [
            'address_type' => $this->faker->randomElement(TiersAddressType::class),
            'address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'address_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
