<?php

namespace Database\Factories;

use App\Models\Article\Article;
use App\Models\Article\ArticleSerialNumber;
use App\Models\Article\Ouvrage;
use App\Models\Core\Warehouse;
use App\Models\Stock\StockMouvement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockMouvementFactory extends Factory
{
    protected $model = StockMouvement::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'adjustement_type' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'unit_cost_ht' => $this->faker->randomFloat(),
            'reference' => $this->faker->word(),
            'note' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'article_id' => Article::factory(),
            'warehouse_id' => Warehouse::factory(),
            'target_warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'serial_number_id' => ArticleSerialNumber::factory(),
            'ouvrage_id' => Ouvrage::factory(),
        ];
    }
}
