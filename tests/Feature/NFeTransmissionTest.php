<?php

namespace Tests\Feature;

use App\Models\Nfe;
use App\Services\Fiscal\NFeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class NFeTransmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_transmit_nfe()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup Data
        // Setup Address, Company (with certificate), Customer (manual creation)
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

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
            'user_id' => $user->id,
            'razao_social' => 'Emitente Ltda',
            'cnpj' => '12345678000199',
            'address_id' => $address->id,
            'regime_tributario' => '1',
            'ie' => '123'
        ]);

        $customer = \App\Models\Customer::create([
            'razao_social' => 'Destinatario Ltda',
            'cpf_cnpj' => '11122233344',
            'address_id' => $address->id,
            'indicador_ie' => '9',
            'company_id' => $company->id
        ]);

        // Mock Certificate path on company/relation usually needs actual file or mock Storage
        // We will mock the NFeService->transmit directly to avoid complex Storage/Certificate/Soap mocking which is integration heavy.

        $nfe = \App\Models\Nfe::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'numero' => 101,
            'serie' => 1,
            'status' => 'signed',
            'valor_total' => 100.00,
            'xml_path' => 'xmls/mock.xml'
        ]);

        // 2. Mock NFeService
        $this->mock(NFeService::class, function ($mock) use ($nfe) {
            $mock->shouldReceive('transmit')
                ->once()
                ->with(Mockery::on(function ($arg) use ($nfe) {
                    return $arg->id === $nfe->id;
                }))
                ->andReturnUsing(function ($arg) {
                    $arg->status = 'authorized';
                    $arg->save();
                    return $arg->toArray();
                });
        });

        // 3. Request
        $response = $this->post(route('nfe.transmit', $nfe));

        // 4. Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nves', [
            'id' => $nfe->id,
            'status' => 'authorized'
        ]);
    }
}
