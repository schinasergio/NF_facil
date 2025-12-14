<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_can_create_company_with_address(): void
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $data = [
            'razao_social' => 'Empresa Teste Ltda',
            'nome_fantasia' => 'Fantasia Teste',
            'cnpj' => '12.345.678/0001-99',
            'ie' => '123456789',
            'regime_tributario' => '1',
            'email' => 'contato@teste.com',
            'telefone' => '1199999999',
            // Address
            'logradouro' => 'Rua das Flores',
            'numero' => '123',
            'bairro' => 'Centro',
            'cep' => '01001-000',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'pais' => 'Brasil',
        ];

        $response = $this->post(route('companies.store'), $data);

        $response->assertRedirect(route('companies.index'));

        $this->assertDatabaseHas('companies', [
            'cnpj' => '12.345.678/0001-99',
            'razao_social' => 'Empresa Teste Ltda',
        ]);

        $this->assertDatabaseHas('addresses', [
            'logradouro' => 'Rua das Flores',
            'numero' => '123',
            'cep' => '01001-000',
        ]);
    }
}
