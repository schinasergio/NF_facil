<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\NfeInutilization;
use App\Services\Fiscal\InutilizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class InutilizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    }

    public function test_can_access_inutilization_page()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('nfe.inutilization.create'))->assertStatus(200);
    }

    public function test_inutilization_request_success()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]); // Ensure user owns company

        // Mock Service
        $this->mock(InutilizationService::class, function ($mock) {
            $mock->shouldReceive('inutilize')
                ->once()
                ->andReturn([
                    'status' => 'authorized',
                    'message' => 'Inutilização Homologada',
                    'protocolo' => '135230000000000',
                    'record' => new NfeInutilization()
                ]);
        });

        $response = $this->actingAs($user)->post(route('nfe.inutilization.store'), [
            'serie' => 1,
            'numero_inicial' => 100,
            'numero_final' => 105,
            'justificativa' => 'Erro de numeração no sistema emissor'
        ]);

        $response->assertRedirect(route('nfe.index'));
        $response->assertSessionHas('success');
    }

    public function test_validation_rules()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('nfe.inutilization.store'), [
            'serie' => 1,
            'numero_inicial' => 100,
            'numero_final' => 99, // Invalid range
            'justificativa' => 'Curto' // Too short
        ]);

        $response->assertSessionHasErrors(['numero_final', 'justificativa']);
    }
}
