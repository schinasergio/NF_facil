<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->word,
            'codigo_sku' => $this->faker->unique()->ean8,
            'ncm' => '12345678',
            'cest' => null,
            'unidade' => 'UN',
            'preco_venda' => $this->faker->randomFloat(2, 10, 1000),
            'origem' => 0,
            'ativo' => true,
        ];
    }
}
