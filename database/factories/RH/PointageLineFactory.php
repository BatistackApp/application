<?php

namespace Database\Factories\RH;

use App\Enums\RH\Periode;
use App\Enums\RH\TypeHeure;
use App\Models\Chantier\Chantier;
use App\Models\RH\PointageLine;
use App\Models\RH\PointageSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class PointageLineFactory extends Factory
{
    protected $model = PointageLine::class;

    public function definition(): array
    {
        return [
            'pointage_session_id' => PointageSession::factory(),
            'chantier_id' => Chantier::factory(),
            'date' => now()->toDateString(),
            'periode' => Periode::MATIN,
            'type_heure' => TypeHeure::NORMALE,
            'heures' => 3.50,
            'heures_trajet' => 0,
            'panier_repas' => false,
            'grand_deplacement' => false,
            'note' => null,
        ];
    }

    public function matin(float $heures = 3.50): static
    {
        return $this->state([
            'periode' => Periode::MATIN,
            'heures' => $heures,
        ]);
    }

    public function aprem(float $heures = 4.00): static
    {
        return $this->state([
            'periode' => Periode::APREM,
            'heures' => $heures,
        ]);
    }

    public function journeeComplete(float $heures = 7.50): static
    {
        return $this->state([
            'periode' => Periode::JOURNEE_COMPLETE,
            'heures' => $heures,
        ]);
    }

    public function withTrajet(float $heures = 1.0): static
    {
        return $this->state(['heures_trajet' => $heures]);
    }

    public function withPanierRepas(): static
    {
        return $this->state([
            'panier_repas' => true,
            'grand_deplacement' => false,
        ]);
    }

    public function withGrandDeplacement(): static
    {
        return $this->state([
            'grand_deplacement' => true,
            'panier_repas' => false,
        ]);
    }

    public function absence(TypeHeure $type = TypeHeure::CONGES): static
    {
        return $this->state([
            'chantier_id' => null,
            'type_heure' => $type,
            'heures' => 7.50,
        ]);
    }
}
