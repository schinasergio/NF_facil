<?php

namespace Tests\Feature;

use App\Services\Fiscal\NFeService;
use App\Models\Nfe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class NFeCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->withoutExceptionHandling();
    }

    public function test_can_cancel_authorized_nfe()
    {
        // 1. Setup Data
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $address = \App\Models\Address::create(['logradouro' => 'L', 'numero' => '1', 'bairro' => 'B', 'cep' => '1', 'cidade' => 'C', 'uf' => 'UF', 'pais' => 'P']);
        $company = \App\Models\Company::create(['razao_social' => 'A', 'cnpj' => '12345678000199', 'address_id' => $address->id, 'regime_tributario' => '1', 'ie' => '1', 'user_id' => $user->id]);
        $customer = \App\Models\Customer::create(['razao_social' => 'C', 'cpf_cnpj' => '11122233344', 'address_id' => $address->id, 'company_id' => $company->id]);

        $nfe = \App\Models\Nfe::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'numero' => 999,
            'serie' => 1,
            'status' => 'authorized', // Must be authorized
            'valor_total' => 100.00,
            'xml_path' => 'mock.xml',
            'protocolo' => '123456789012345',
            'chave' => '35230112345678000199550010000009991000000000'
        ]);

        // 2. Mock NFeService
        $this->mock(NFeService::class, function ($mock) use ($nfe) {
            $mock->shouldReceive('cancel')
                ->once()
                ->with(
                    Mockery::on(fn($arg) => $arg->id === $nfe->id),
                    'Erro de emissão detectado no valor'
                )
                ->andReturnUsing(function ($arg) {
                    $arg->status = 'canceled';
                    $arg->save(); // Simulate service side effect
                    return $arg->toArray();
                });
        });

        // 3. Request
        $response = $this->post(route('nfe.cancel', $nfe), [
            'justification' => 'Erro de emissão detectado no valor'
        ]);

        // 4. Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nves', [
            'id' => $nfe->id,
            'status' => 'canceled'
        ]);
    }

    public function test_cannot_cancel_unauthorized_nfe()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $address = \App\Models\Address::create(['logradouro' => 'L', 'numero' => '1', 'bairro' => 'B', 'cep' => '1', 'cidade' => 'C', 'uf' => 'UF', 'pais' => 'P']);
        $company = \App\Models\Company::create(['razao_social' => 'A', 'cnpj' => '12345678000199', 'address_id' => $address->id, 'regime_tributario' => '1', 'ie' => '1', 'user_id' => $user->id]);
        $customer = \App\Models\Customer::create(['razao_social' => 'C', 'cpf_cnpj' => '11122233344', 'address_id' => $address->id, 'company_id' => $company->id]);

        $nfe = \App\Models\Nfe::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'numero' => 999,
            'serie' => 1,
            'status' => 'signed',
            'valor_total' => 100.00
        ]);

        // Real service call or mock throwing exception
        // Here we can rely on real logic validation in Controller <-> Service 
        // But since we mock Service usually to avoid File read, let's mock the exception

        $this->mock(NFeService::class, function ($mock) {
            $mock->shouldReceive('cancel')->andThrow(new \Exception("Apenas NFes autorizadas podem ser canceladas."));
        });

        $response = $this->post(route('nfe.cancel', $nfe), [
            'justification' => 'Erro de emissão detectado no valor'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
