<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Nfe;
use App\Models\User;
use App\Services\Fiscal\NFeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class NFeCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock storage to avoid actual file operations
        Storage::fake('local');
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    }

    public function test_can_access_correction_page()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $nfe = Nfe::create([
            'company_id' => $company->id,
            'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id,
            'status' => 'authorized', // Only authorized can be corrected
            'numero' => 100,
            'serie' => 1,
            'xml_path' => 'xmls/test.xml',
            'valor_total' => 100.00
        ]);

        $response = $this->actingAs($user)->get(route('nfe.correction', $nfe->id));

        $response->assertStatus(200);
        $response->assertViewIs('nfe.correction');
    }

    public function test_correction_request_success()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $nfe = Nfe::create([
            'company_id' => $company->id,
            'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id,
            'status' => 'authorized',
            'numero' => 100,
            'serie' => 1,
            'chave' => '35230112345678000199550010000001001000000000',
            'protocolo' => '135230000000000',
            'xml_path' => 'xmls/test.xml',
            'valor_total' => 100.00
        ]);

        // Mock Service
        $this->mock(NFeService::class, function ($mock) use ($nfe) {
            $mock->shouldReceive('correction')
                ->once()
                ->with(Mockery::on(function ($arg) use ($nfe) {
                    return $arg->id === $nfe->id;
                }), 'Correção de teste com mais de 15 caracteres')
                ->andReturn(['status' => 'corrected', 'event_xml' => 'mock_event_xml']);
        });

        $response = $this->actingAs($user)->post(route('nfe.correction.store', $nfe->id), [
            'correction_text' => 'Correção de teste com mais de 15 caracteres'
        ]);

        $response->assertRedirect(route('nfe.index'));
        $response->assertSessionHas('success');
    }

    public function test_correction_validation_min_length()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $nfe = Nfe::create([
            'company_id' => $company->id,
            'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id,
            'status' => 'authorized',
            'numero' => 102,
            'serie' => 1,
            'valor_total' => 100.00
        ]);

        $response = $this->actingAs($user)->post(route('nfe.correction.store', $nfe->id), [
            'correction_text' => 'Curto' // Too short
        ]);

        $response->assertSessionHasErrors('correction_text');
    }
}
