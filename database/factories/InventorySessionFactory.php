<?php

namespace Database\Factories;

use App\Models\Core\Warehouse;
use App\Models\Stock\InventorySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InventorySessionFactory extends Factory
{
    protected $model = InventorySession::class;

    public function definition(): array
    {
        return [
            'reference' => $this->faker->word(),
            'status' => $this->faker->word(),
            'opened_at' => Carbon::now(),
            'closed_at' => Carbon::now(),
            'validated_at' => Carbon::now(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'warehouse_id' => Warehouse::factory(),
            'created_by' => User::factory(),
            'validated_by' => User::factory(),
        ];
    }
}
