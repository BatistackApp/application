<?php

namespace Database\Factories;

use App\Models\Core\CompanyInfo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CompanyInfoFactory extends Factory
{
    protected $model = CompanyInfo::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'adresse' => $this->faker->streetAddress(),
            'code_postal' => $this->faker->postcode(),
            'ville' => $this->faker->city(),
            'pays' => $this->faker->country(),
            'siret' => $this->faker->numerify('##############'),
            'num_tva' => $this->faker->numerify('FR###########'),
            'ape' => $this->faker->numerify('###Z'),
            'telephone' => $this->faker->phoneNumber(),
            'fax' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'site_web' => $this->faker->domainName(),
            'logo_path' => $this->faker->imageUrl(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
