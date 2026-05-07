<?php

namespace Database\Factories\RH;

use App\Enums\RH\PointageStatus;
use App\Models\RH\Employee;
use App\Models\RH\PointageSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PointageSessionFactory extends Factory
{
    protected $model = PointageSession::class;

    public function definition(): array
    {
        // Toujours un lundi
        $lundi = now()->startOfWeek()->subWeeks(
            $this->faker->numberBetween(0, 8)
        );

        return [
            'employee_id' => Employee::factory(),
            'semaine_du' => $lundi->toDateString(),
            'status' => PointageStatus::DRAFT,
            'submitted_at' => null,
            'validated_at' => null,
            'rejected_at' => null,
            'imputed_at' => null,
            'validated_by' => null,
            'rejection_reason' => null,
            'notes' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state([
            'status' => PointageStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function validated(): static
    {
        return $this->state([
            'status' => PointageStatus::VALIDATED,
            'submitted_at' => now()->subHours(2),
            'validated_at' => now(),
            'validated_by' => User::factory(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => PointageStatus::REJECTED,
            'submitted_at' => now()->subHours(3),
            'rejected_at' => now(),
            'rejection_reason' => 'Heures incorrectes sur le chantier CH-2026-001.',
        ]);
    }

    public function imputed(): static
    {
        return $this->state([
            'status' => PointageStatus::IMPUTED,
            'submitted_at' => now()->subDays(2),
            'validated_at' => now()->subDay(),
            'imputed_at' => now(),
            'validated_by' => User::factory(),
        ]);
    }

    public function forWeek(Carbon $lundi): static
    {
        return $this->state(['semaine_du' => $lundi->toDateString()]);
    }
}
