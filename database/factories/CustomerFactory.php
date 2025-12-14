<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'address_id' => \App\Models\Address::factory(),
            'razao_social' => $this->faker->name,
            'cpf_cnpj' => $this->faker->numerify('###########'), // CPF 11 digits
            'email' => $this->faker->email,
        ];
    }
}
