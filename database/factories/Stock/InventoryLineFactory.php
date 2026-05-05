<?php

namespace Database\Factories\Stock;

use App\Models\Article\Article;
use App\Models\Stock\InventoryLine;
use App\Models\Stock\InventorySession;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryLineFactory extends Factory
{
    protected $model = InventoryLine::class;

    public function definition(): array
    {
        return [
            'inventory_session_id' => InventorySession::factory(),
            'article_id' => Article::factory(),
            'theoretical_quantity' => $this->faker->randomFloat(3, 1, 100),
            'counted_quantity' => null,
        ];
    }

    public function counted(float $quantity): static
    {
        return $this->state(['counted_quantity' => $quantity]);
    }
}
