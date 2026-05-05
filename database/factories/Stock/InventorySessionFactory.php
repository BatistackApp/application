<?php

namespace Database\Factories\Stock;

use App\Enums\Article\InventorySessionStatus;
use App\Models\Core\Warehouse;
use App\Models\Stock\InventorySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventorySessionFactory extends Factory
{
    protected $model = InventorySession::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'reference' => 'INV-'.now()->year.'-'.str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'status' => InventorySessionStatus::OPEN,
            'opened_at' => now(),
            'closed_at' => null,
            'validated_at' => null,
            'created_by' => User::factory(),
            'validated_by' => null,
            'notes' => null,
        ];
    }

    public function counting(): static
    {
        return $this->state(['status' => InventorySessionStatus::COUNTING]);
    }

    public function closed(): static
    {
        return $this->state([
            'status' => InventorySessionStatus::CLOSED,
            'closed_at' => now(),
        ]);
    }

    public function validated(): static
    {
        return $this->state([
            'status' => InventorySessionStatus::VALIDATED,
            'closed_at' => now(),
            'validated_at' => now(),
            'validated_by' => User::factory(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => InventorySessionStatus::CANCELLED]);
    }
}
