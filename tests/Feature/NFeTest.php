<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Fiscal\NFeService;
use App\Models\Nfe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class NFeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_nfe()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup Data
        // 1. Setup Data
        $address = \App\Models\Address::create([
            'logradouro' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Bairro',
            'cep' => '12345678',
            'cidade' => 'Cidade',
            'uf' => 'SP',
            'pais' => 'Brasil'
        ]);

        $company = \App\Models\Company::create([
            'razao_social' => 'Emitente Ltda',
            'nome_fantasia' => 'Emitente',
            'cnpj' => '12345678000199',
            'address_id' => $address->id,
            'regime_tributario' => '1',
            'ie' => '123456789'
        ]);

        $customer = \App\Models\Customer::create([
            'razao_social' => 'Destinatario Ltda',
            'cpf_cnpj' => '11122233344',
            'address_id' => $address->id,
            'indicador_ie' => '9'
        ]);

        $product = \App\Models\Product::create([
            'nome' => 'Prod Teste',
            'preco_venda' => 10.00,
            'ncm' => '12345678',
            'unidade' => 'UN',
            'origem' => 0,
            'codigo_sku' => 'SKU123'
        ]);


        // 2. Mock NFeService
        $this->mock(NFeService::class, function ($mock) {
            $mock->shouldReceive('generate')
                ->once()
                ->andReturn(new Nfe([
                    'numero' => 100,
                    'serie' => 1,
                    'status' => 'signed',
                    'valor_total' => 10.00
                ]));
        });

        // 3. Request
        $response = $this->post(route('nfe.store'), [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id]
            ]
        ]);

        // 4. Assert
        $response->assertRedirect(route('nfe.index'));
        $response->assertSessionHas('success');
    }
}
