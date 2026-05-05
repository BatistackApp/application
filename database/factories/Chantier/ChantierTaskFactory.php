<?php

namespace Database\Factories\Chantier;

use App\Enums\Chantier\ChantierTaskStatus;
use App\Models\Chantier\Chantier;
use App\Models\Chantier\ChantierTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChantierTask>
 */
class ChantierTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $debut = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $fin = $this->faker->dateTimeBetween($debut, '+3 months');

        return [
            'chantier_id' => Chantier::factory(),
            'parent_task_id' => null,
            'depends_on_task_id' => null,
            'assignee_id' => User::factory(),
            'designation' => $this->faker->words(4, true),
            'description' => $this->faker->sentence(),
            'status' => ChantierTaskStatus::TODO,
            'date_debut' => $debut,
            'date_fin' => $fin,
            'avancement_pct' => 0,
            'ordre' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function inProgress(int $avancement = 50): static
    {
        return $this->state([
            'status' => ChantierTaskStatus::IN_PROGRESS,
            'avancement_pct' => $avancement,
        ]);
    }

    public function done(): static
    {
        return $this->state([
            'status' => ChantierTaskStatus::DONE,
            'avancement_pct' => 100,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(['status' => ChantierTaskStatus::BLOCKED]);
    }
}
