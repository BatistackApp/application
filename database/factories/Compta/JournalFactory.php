<?php

namespace Database\Factories\Compta;

use App\Enums\Compta\JournalType;
use App\Models\Compta\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(JournalType::cases());

        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'libelle' => $this->faker->words(2, true),
            'type' => $type,
            'actif' => true,
        ];
    }

    public function ventes(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'VE',
            'libelle' => 'Ventes',
            'type' => JournalType::VENTES,
        ]);
    }

    public function achats(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'AC',
            'libelle' => 'Achats',
            'type' => JournalType::ACHATS,
        ]);
    }

    public function banque(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'BQ',
            'libelle' => 'Banque',
            'type' => JournalType::BANQUE,
        ]);
    }

    public function od(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'OD',
            'libelle' => 'Opérations diverses',
            'type' => JournalType::OPERATIONS_DIVERSES,
        ]);
    }

    public function paie(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'PAIE',
            'libelle' => 'Paie',
            'type' => JournalType::PAIE,
        ]);
    }
}
