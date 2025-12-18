<?php

namespace App\Services\Cadastros;

use App\Models\Product;

class ProductService
{
    /**
     * Create a new product.
     */
    public function createProduct(array $data): Product
    {
        file_put_contents('/tmp/product_debug.log', "Data passed to create: " . print_r($data, true), FILE_APPEND);
        return Product::create($data);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }
}
