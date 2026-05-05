<?php

namespace Database\Factories\Article;

use App\Enums\Article\TrackingType;
use App\Enums\UnitOfMesure;
use App\Models\Article\Article;
use App\Models\Article\ArticleCategory;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'sku' => $this->faker->word(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'unit' => $this->faker->randomElement(UnitOfMesure::class),
            'tracking_type' => $this->faker->randomElement(TrackingType::class),
            'barcode' => $this->faker->word(),
            'qr_code_base' => $this->faker->word(),
            'poids' => $this->faker->randomFloat(),
            'volume' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'article_category_id' => ArticleCategory::factory(),
            'default_supplier_id' => Tiers::factory(),
        ];
    }
}
