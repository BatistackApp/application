<?php

namespace Database\Factories\Article;

use App\Models\Article\Article;
use App\Models\Article\ArticlePrice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ArticlePriceFactory extends Factory
{
    protected $model = ArticlePrice::class;

    public function definition(): array
    {
        return [
            'price_type' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'article_id' => Article::factory(),
        ];
    }
}
