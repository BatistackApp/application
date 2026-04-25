<?php

namespace Database\Factories;

use App\Enums\Civility;
use App\Enums\Tiers\TiersCategory;
use App\Enums\Tiers\TiersTypology;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TiersFactory extends Factory
{
    protected $model = Tiers::class;

    public function definition(): array
    {
        $siren = $this->faker->unique()->numerify('#########');
        $category = $this->faker->randomElement(TiersCategory::class);
        $prefixCode = match ($category) {
            'customer' => 'CUS',
            'supplier' => 'SP',
            'subcontractor' => 'SU',
            'other' => 'OTH',
        };

        return [
            'civility' => $this->faker->randomElement(Civility::class),
            'name' => $this->faker->company(),
            'typology' => $this->faker->randomElement(TiersTypology::class),
            'category' => $category,
            'siren' => $siren,
            'naf' => $this->faker->numerify('####Z'),
            'num_tva' => $this->faker->numerify('FR##'.$siren),
            'code' => $prefixCode.'-'.$this->faker->randomNumber(5),
            'dgpd_concilient' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
