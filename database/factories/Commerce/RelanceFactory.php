<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\Relance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelanceFactory extends Factory
{
    protected $model = Relance::class;

    public function definition(): array
    {
        return [
            'facture_id' => CommercialDocument::factory()->facture(),
            'user_id' => User::factory(),
            'date_relance' => fake()->dateTimeBetween('-1 month', 'now'),
            'type' => fake()->randomElement(['email', 'courrier', 'appel']),
            'contenu' => fake()->paragraph(),
            'reponse_client' => fake()->optional()->sentence(),
        ];
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
            'contenu' => 'Relance automatique par email concernant la facture impayée.',
        ]);
    }

    public function courrier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'courrier',
            'contenu' => 'Courrier de relance envoyé par voie postale.',
        ]);
    }

    public function appel(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'appel',
            'contenu' => 'Relance téléphonique effectuée.',
        ]);
    }

    public function withReponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'reponse_client' => fake()->sentence(),
        ]);
    }
}
