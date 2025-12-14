<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Certificate;
use App\Models\Address;
use App\Services\Fiscal\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_certificate()
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Setup Data
        $address = Address::create([
            'logradouro' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Bairro',
            'cep' => '12345678',
            'cidade' => 'Cidade',
            'uf' => 'UF',
            'pais' => 'Brasil'
        ]);
        $company = Company::create([
            'address_id' => $address->id,
            'razao_social' => 'Empresa Teste',
            'cnpj' => '12345678000199',
            'ie' => '123',
            'regime_tributario' => '1'
        ]);

        // 2. Mock Service to avoid needing real PFX
        $this->mock(CertificateService::class, function ($mock) use ($company) {
            $mock->shouldReceive('uploadCertificate')
                ->once()
                ->andReturn(new Certificate([
                    'company_id' => $company->id,
                    'path' => 'certificates/dummy.pfx',
                    'password' => 'secret',
                    'expires_at' => now()->addYear(),
                ]));
        });

        // 3. Perform Request
        $file = UploadedFile::fake()->create('cert.pfx', 10, 'application/x-pkcs12');

        $response = $this->post(route('companies.certificate.store', $company), [
            'pfx_file' => $file,
            'password' => '123456',
        ]);

        // 4. Assert Redirect
        $response->assertRedirect(route('companies.index'));
        $response->assertSessionHas('success');
    }
}
