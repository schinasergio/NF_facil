<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful creation of a product.
     */
    public function test_can_create_product(): void
    {
        // Disable CSRF for testing
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $data = [
            'nome' => 'Produto Teste',
            'codigo_sku' => 'SKU-001',
            'ncm' => '12345678', // 8 digits
            'cest' => '1234567', // 7 digits
            'unidade' => 'UN',
            'preco_venda' => 100.00,
            'origem' => 0,
        ];

        $response = $this->post(route('products.store'), $data);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'nome' => 'Produto Teste',
            'codigo_sku' => 'SKU-001',
            'ncm' => '12345678',
        ]);
    }
}
