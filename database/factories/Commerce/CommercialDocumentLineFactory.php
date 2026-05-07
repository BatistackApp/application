<?php

namespace Database\Factories\Commerce;

use App\Enums\Commerce\TauxTva;
use App\Models\Article\Article;
use App\Models\Article\Ouvrage;
use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\CommercialDocumentLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommercialDocumentLineFactory extends Factory
{
    protected $model = CommercialDocumentLine::class;

    public function definition(): array
    {
        $quantite = fake()->randomFloat(2, 1, 100);
        $prixUnitaire = fake()->randomFloat(2, 10, 500);

        return [
            'commercial_document_id' => CommercialDocument::factory(),
            'article_id' => null,
            'ouvrage_id' => null,
            'designation' => fake()->sentence(6),
            'quantite' => $quantite,
            'unite' => fake()->randomElement(['m²', 'm³', 'ml', 'u', 'kg']),
            'prix_unitaire_ht' => $prixUnitaire,
            'taux_tva' => TauxTva::TVA_20->getRate(),
            'remise_pct' => 0,
            'remise_montant' => 0,
            'ordre' => 0,
        ];
    }

    public function withArticle(): static
    {
        return $this->state(function (array $attributes) {
            $article = Article::factory()->create();

            return [
                'article_id' => $article->id,
                'designation' => $article->name,
                'unite' => $article->unit->getAbrv(),
                'prix_unitaire_ht' => $article->prix_vente_ht ?? fake()->randomFloat(2, 10, 500),
            ];
        });
    }

    public function withOuvrage(): static
    {
        return $this->state(function (array $attributes) {
            $ouvrage = Ouvrage::factory()->create();

            return [
                'ouvrage_id' => $ouvrage->id,
                'designation' => $ouvrage->code.' - '.$ouvrage->label,
                'unite' => $ouvrage->unit->getAbrv(),
                'prix_unitaire_ht' => $ouvrage->prix_total_ht ?? fake()->randomFloat(2, 50, 2000),
            ];
        });
    }

    public function withRemise(): static
    {
        return $this->state(fn (array $attributes) => [
            'remise_pct' => fake()->randomFloat(2, 5, 20),
        ]);
    }

    public function tvaReduite(): static
    {
        return $this->state(fn (array $attributes) => [
            'taux_tva' => TauxTva::TVA_10->getRate(),
        ]);
    }

    public function tvaExoneree(): static
    {
        return $this->state(fn (array $attributes) => [
            'taux_tva' => TauxTva::EXONERE->getRate(),
        ]);
    }
}
