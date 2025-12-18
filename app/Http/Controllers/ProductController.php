<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cadastros\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreProductRequest $request)
    {
        file_put_contents('/tmp/controller_store_debug.log', "Controller Store Reached\n", FILE_APPEND);
        $validated = $request->validated();
        file_put_contents('/tmp/controller_store_debug.log', "Validation Passed\n", FILE_APPEND);

        // Assign to first company of user (Multi-tenant simplification)
        // TODO: Allow selecting company if user has multiple
        $company = \App\Models\Company::where('user_id', auth()->id())->firstOrFail();
        file_put_contents('/tmp/controller_store_debug.log', "Company Found: {$company->id}\n", FILE_APPEND);
        $validated['company_id'] = $company->id;

        $this->productService->createProduct($validated);

        return redirect()->route('products.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\StoreProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $this->productService->updateProduct($product, $validated);

        return redirect()->route('products.index')->with('success', 'Produto atualizado!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->productService->deleteProduct($product);
        return redirect()->route('products.index')->with('success', 'Produto removido.');
    }
}
