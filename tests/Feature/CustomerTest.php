<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful creation of a customer with address.
     */
    public function test_can_create_customer_with_address(): void
    {
        // Disable CSRF for testing
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $data = [
            'razao_social' => 'Cliente Exemplo Ltda',
            'nome_fantasia' => 'Loja do Cliente',
            'cpf_cnpj' => '11.111.111/0001-11',
            'ie' => '987654321',
            'indicador_ie' => '1',
            'email' => 'cliente@exemplo.com',
            'telefone' => '1188888888',
            // Address
            'logradouro' => 'Av. Paulista',
            'numero' => '1000',
            'bairro' => 'Bela Vista',
            'cep' => '01310-100',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'pais' => 'Brasil',
        ];

        $response = $this->post(route('customers.store'), $data);

        $response->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', [
            'cpf_cnpj' => '11.111.111/0001-11',
            'razao_social' => 'Cliente Exemplo Ltda',
        ]);

        $this->assertDatabaseHas('addresses', [
            'logradouro' => 'Av. Paulista',
            'numero' => '1000',
            'cep' => '01310-100',
        ]);
    }
}
