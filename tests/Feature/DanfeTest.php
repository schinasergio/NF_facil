<?php

namespace Tests\Feature;

use App\Services\Fiscal\DanfeService;
use App\Models\Nfe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class DanfeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_download_danfe()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup Data
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $address = \App\Models\Address::create(['logradouro' => 'L', 'numero' => '1', 'bairro' => 'B', 'cep' => '1', 'cidade' => 'C', 'uf' => 'UF', 'pais' => 'P']);
        $company = \App\Models\Company::create(['razao_social' => 'A', 'cnpj' => '12345678000199', 'address_id' => $address->id, 'regime_tributario' => '1', 'ie' => '1', 'user_id' => $user->id]);
        $customer = \App\Models\Customer::create(['razao_social' => 'C', 'cpf_cnpj' => '11122233344', 'address_id' => $address->id, 'company_id' => $company->id]);
        $nfe = \App\Models\Nfe::create(['company_id' => $company->id, 'customer_id' => $customer->id, 'numero' => 999, 'serie' => 1, 'status' => 'authorized', 'valor_total' => 100.00, 'xml_path' => 'mock.xml']);

        // 2. Mock DanfeService
        $this->mock(DanfeService::class, function ($mock) {
            $mock->shouldReceive('generatePdf')
                ->once()
                ->withAnyArgs()
                ->andReturn('PDF_BINARY_CONTENT');
        });

        // 3. Request
        $response = $this->get(route('nfe.pdf', $nfe));
        // 4. Assert
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'attachment; filename=nfe-999.pdf')
            || str_contains($response->headers->get('Content-Disposition'), 'attachment; filename="nfe-999.pdf"'));
    }
}
