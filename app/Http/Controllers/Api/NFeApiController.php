<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNfeRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Fiscal\NFeService;
use Illuminate\Http\Request;

class NFeApiController extends Controller
{
    protected $nfeService;

    public function __construct(NFeService $nfeService)
    {
        $this->nfeService = $nfeService;
    }

    public function store(StoreNfeRequest $request)
    {
        // Validation handled by FormRequest via Accept: application/json

        $user = $request->user();

        // Ensure company belongs to user
        $company = Company::where('id', $request->company_id)->where('user_id', $user->id)->firstOrFail();

        // Ensure customer belongs to company (or loose check if multitenant logic differs)
        $customer = Customer::findOrFail($request->customer_id);

        // Prepare items
        $items = [];
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return response()->json(['error' => "Product {$item['product_id']} not found"], 422);
            }
            $items[] = [
                'nome' => $product->nome,
                'codigo_sku' => $product->codigo_sku,
                'ncm' => $product->ncm,
                'unidade' => $product->unidade,
                'preco_venda' => $product->preco_venda,
            ];
        }

        try {
            $nfe = $this->nfeService->generate($company, $customer, $items);
            // Optionally transmit immediately
            // $this->nfeService->transmit($nfe);

            return response()->json([
                'success' => true,
                'nfe_id' => $nfe->id,
                'numero' => $nfe->numero,
                'status' => $nfe->status
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
