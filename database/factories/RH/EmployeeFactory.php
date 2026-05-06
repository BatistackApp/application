<?php

namespace Database\Factories\RH;

use App\Enums\RH\JourSemaine;
use App\Enums\RH\TypeContrat;
use App\Models\RH\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'matricule' => 'EMP-'.str_pad(
                $this->faker->unique()->numberBetween(1, 9999),
                4, '0', STR_PAD_LEFT
            ),
            'type_contrat' => TypeContrat::CDI,
            'taux_horaire' => $this->faker->randomFloat(2, 12, 35),
            'date_embauche' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            'date_fin_contrat' => null,
            'jours_travailles' => null,
            'is_active' => true,
        ];
    }

    public function cdd(?string $dateFin = null): static
    {
        return $this->state([
            'type_contrat' => TypeContrat::CDD,
            'date_fin_contrat' => $dateFin ?? now()->addMonths(6)->toDateString(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withSamedi(): static
    {
        return $this->state([
            'jours_travailles' => [
                ...JourSemaine::defaultJours(),
                JourSemaine::SAMEDI->value,
            ],
        ]);
    }
}
