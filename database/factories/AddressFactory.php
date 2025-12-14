<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Address;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'logradouro' => $this->faker->streetName,
            'numero' => $this->faker->buildingNumber,
            'bairro' => $this->faker->citySuffix,
            'cep' => $this->faker->numerify('########'),
            'cidade' => $this->faker->city,
            'uf' => $this->faker->stateAbbr,
            'pais' => 'Brasil',
        ];
    }
}
