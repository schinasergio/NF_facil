<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rejects_unauthenticated_requests()
    {
        $response = $this->postJson('/api/nfe', []);
        $response->assertStatus(401);
    }

    public function test_api_can_generate_nfe()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create();

        // Mock NFeService
        $nfeServiceMock = $this->mock(\App\Services\Fiscal\NFeService::class, function ($mock) use ($company, $customer) {
            $mock->shouldReceive('generate')
                ->once()
                ->withArgs(function ($c, $cust, $items) use ($company, $customer) {
                    return $c->id === $company->id && $cust->id === $customer->id && count($items) === 1;
                })
                ->andReturn(new \App\Models\Nfe([
                    'id' => 1,
                    'company_id' => $company->id,
                    'customer_id' => $customer->id,
                    'numero' => 123,
                    'status' => 'created'
                ]));
        });

        Sanctum::actingAs($user);

        $payload = [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantidade' => 10
                ]
            ],
            'natureza_operacao' => 'Venda',
            'forma_pagamento' => 'VISTA'
        ];

        $response = $this->postJson('/api/nfe', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'numero' => 123
            ]);
    }
}
