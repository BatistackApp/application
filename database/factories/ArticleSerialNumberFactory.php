<?php

namespace Database\Factories;

use App\Models\Article\Article;
use App\Models\Article\ArticleSerialNumber;
use App\Models\Core\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ArticleSerialNumberFactory extends Factory
{
    protected $model = ArticleSerialNumber::class;

    public function definition(): array
    {
        return [
            'serial_number' => $this->faker->word(),
            'status' => $this->faker->word(),
            'photo_plate_path' => $this->faker->word(),
            'purchase_date' => Carbon::now(),
            'warranty_expiry' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'article_id' => Article::factory(),
            'warehouse_id' => Warehouse::factory(),
            'assigned_user_id' => User::factory(),
        ];
    }
}
