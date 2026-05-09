<?php

namespace Database\Factories\Compta;

use App\Models\Compta\ExerciceComptable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciceComptableFactory extends Factory
{
    protected $model = ExerciceComptable::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2023, 2026);
        $dateDebut = Carbon::create($year, 1, 1);
        $dateFin = Carbon::create($year, 12, 31);

        return [
            'libelle' => "Exercice {$year}",
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'cloture' => false,
            'date_cloture' => null,
        ];
    }

    public function cloture(): static
    {
        return $this->state(fn (array $attributes) => [
            'cloture' => true,
            'date_cloture' => $attributes['date_fin'],
        ]);
    }

    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_debut' => now()->startOfYear(),
            'date_fin' => now()->endOfYear(),
            'cloture' => false,
        ]);
    }
}
