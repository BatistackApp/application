<?php

namespace Database\Factories\Compta;

use App\Enums\Compta\RegimeTva;
use App\Models\Compta\DeclarationTva;
use App\Models\Compta\ExerciceComptable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeclarationTvaFactory extends Factory
{
    protected $model = DeclarationTva::class;

    public function definition(): array
    {
        $regime = RegimeTva::REEL_NORMAL;
        $dateDebut = $this->faker->dateTimeBetween('-1 year', 'now');
        $dateFin = (clone $dateDebut)->modify('+1 month -1 day');

        $baseTva20 = $this->faker->randomFloat(2, 10000, 100000);
        $montantTva20 = $baseTva20 * 0.20;

        $tvaDeductibleBiens = $this->faker->randomFloat(2, 1000, 5000);
        $tvaNette = $montantTva20 - $tvaDeductibleBiens;

        return [
            'exercice_comptable_id' => ExerciceComptable::factory(),
            'periode' => Carbon::parse($dateDebut)->format('Y-m'),
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'regime' => $regime,
            'base_tva_collectee_20' => $baseTva20,
            'montant_tva_collectee_20' => $montantTva20,
            'base_tva_collectee_10' => 0,
            'montant_tva_collectee_10' => 0,
            'base_tva_collectee_55' => 0,
            'montant_tva_collectee_55' => 0,
            'total_tva_collectee' => $montantTva20,
            'tva_deductible_immobilisations' => 0,
            'tva_deductible_biens_services' => $tvaDeductibleBiens,
            'total_tva_deductible' => $tvaDeductibleBiens,
            'tva_nette' => $tvaNette,
            'credit_periode_precedente' => 0,
            'tva_due' => $tvaNette,
            'validee' => false,
        ];
    }

    public function validee(): static
    {
        return $this->state(fn (array $attributes) => [
            'validee' => true,
            'validee_at' => now(),
            'validee_by' => User::factory(),
        ]);
    }

    public function transmise(): static
    {
        return $this->state(fn (array $attributes) => [
            'validee' => true,
            'validee_at' => now()->subDays(5),
            'validee_by' => User::factory(),
            'transmise' => true,
            'transmise_at' => now(),
        ]);
    }

    public function avecCredit(): static
    {
        return $this->state(fn (array $attributes) => [
            'tva_deductible_biens_services' => $attributes['montant_tva_collectee_20'] + 1000,
            'total_tva_deductible' => $attributes['montant_tva_collectee_20'] + 1000,
            'tva_nette' => -1000,
            'tva_due' => -1000,
        ]);
    }

    public function trimestriel(): static
    {
        return $this->state(function (array $attributes) {
            $dateDebut = $this->faker->dateTimeBetween('-1 year', 'now');
            $trimestre = ceil(Carbon::parse($dateDebut)->month / 3);
            $dateFin = (clone $dateDebut)->modify('+3 months -1 day');

            return [
                'regime' => RegimeTva::REEL_SIMPLIFIE,
                'periode' => Carbon::parse($dateDebut)->format('Y').'-T'.$trimestre,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ];
        });
    }
}
