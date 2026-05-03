<?php

namespace Database\Factories;

use App\Models\Article\Article;
use App\Models\Article\Ouvrage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OuvrageFactory extends Factory
{
    protected $model = Ouvrage::class;

    public function definition(): array
    {
        return [
            'sku' => $this->faker->word(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'unit' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'cump_ht' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'article_id' => Article::factory(),
        ];
    }
}
