<?php

namespace Database\Factories\RH;

use App\Enums\RH\JourSemaine;
use App\Models\RH\RhConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class RhConfigurationFactory extends Factory
{
    protected $model = RhConfiguration::class;

    public function definition(): array
    {
        return [
            'heures_matin' => 3.50,
            'heures_aprem' => 4.00,
            'jours_travailles' => JourSemaine::defaultJours(),
            'prise_en_charge_trajet' => false,
            'taux_prise_en_charge_trajet' => 0,
            'grand_deplacement_actif' => false,
            'grand_deplacement_montant_jour' => 0,
            'grand_deplacement_montant_repas' => 0,
            'grand_deplacement_montant_heberg' => 0,
            'panier_repas_actif' => false,
            'panier_repas_montant' => 0,
        ];
    }

    public function withTrajet(float $taux = 75): static
    {
        return $this->state([
            'prise_en_charge_trajet' => true,
            'taux_prise_en_charge_trajet' => $taux,
        ]);
    }

    public function withGrandDeplacement(
        float $jour = 98,
        float $repas = 20.20,
        float $heberg = 77.80,
    ): static {
        return $this->state([
            'grand_deplacement_actif' => true,
            'grand_deplacement_montant_jour' => $jour,
            'grand_deplacement_montant_repas' => $repas,
            'grand_deplacement_montant_heberg' => $heberg,
        ]);
    }

    public function withPanierRepas(float $montant = 10.80): static
    {
        return $this->state([
            'panier_repas_actif' => true,
            'panier_repas_montant' => $montant,
        ]);
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
