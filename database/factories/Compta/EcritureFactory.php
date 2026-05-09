<?php

namespace Database\Factories\Compta;

use App\Enums\Compta\EcritureStatus;
use App\Models\Compta\Ecriture;
use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\Journal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EcritureFactory extends Factory
{
    protected $model = Ecriture::class;

    public function definition(): array
    {
        $exercice = ExerciceComptable::factory();
        $journal = Journal::factory();
        $date = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'exercice_comptable_id' => $exercice,
            'journal_id' => $journal,
            'numero_piece' => $this->generateNumeroPiece(),
            'date_ecriture' => $date,
            'libelle' => $this->faker->sentence(),
            'status' => EcritureStatus::BROUILLON,
            'created_by' => User::factory(),
        ];
    }

    protected function generateNumeroPiece(): string
    {
        $code = strtoupper($this->faker->lexify('??'));
        $year = now()->year;
        $month = str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);
        $seq = str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return "{$code}-{$year}{$month}-{$seq}";
    }

    public function valide(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EcritureStatus::VALIDE,
            'validated_by' => User::factory(),
            'validated_at' => now(),
        ]);
    }

    public function extourne(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EcritureStatus::EXTOURNE,
            'extourne_ecriture_id' => Ecriture::factory()->valide(),
        ]);
    }

    public function withExercice(ExerciceComptable $exercice): static
    {
        return $this->state(fn (array $attributes) => [
            'exercice_comptable_id' => $exercice->id,
            'date_ecriture' => $this->faker->dateTimeBetween($exercice->date_debut, $exercice->date_fin),
        ]);
    }

    public function withJournal(Journal $journal): static
    {
        return $this->state(fn (array $attributes) => [
            'journal_id' => $journal->id,
        ]);
    }
}
