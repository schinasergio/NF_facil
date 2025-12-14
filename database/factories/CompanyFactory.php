<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'address_id' => \App\Models\Address::factory(),
            'razao_social' => $this->faker->company,
            'nome_fantasia' => $this->faker->companySuffix,
            'cnpj' => $this->faker->numerify('##############'), // 14 digits
            'ie' => $this->faker->numerify('#########'),
            'regime_tributario' => '1', // Simples Nacional
        ];
    }
}
