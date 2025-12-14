<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Nfe;
use App\Models\Product;
use App\Services\Fiscal\NFeService;
use Illuminate\Http\Request;

class NFeController extends Controller
{
    protected $nfeService;

    public function __construct(NFeService $nfeService)
    {
        $this->nfeService = $nfeService;
    }

    /**
     * Show form to generate NFe.
     */
    public function create()
    {
        $companies = Company::all(); // Simplified: Select Emitter
        $customers = Customer::all();
        $products = Product::all();
        return view('nfe.create', compact('companies', 'customers', 'products'));
    }

    /**
     * Generate NFe.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
        ]);

        $company = Company::findOrFail($request->company_id);
        $customer = Customer::findOrFail($request->customer_id);

        // Prepare items
        $items = [];
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $items[] = [
                'nome' => $product->nome,
                'codigo_sku' => $product->codigo_sku,
                'ncm' => $product->ncm,
                'unidade' => $product->unidade,
                'preco_venda' => $product->preco_venda, // Use current price
            ];
        }

        try {
            $nfe = $this->nfeService->generate($company, $customer, $items);
            return redirect()->route('nfe.index')->with('success', "NFe #{$nfe->numero} gerada com sucesso!");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function index()
    {
        $nfes = \App\Models\Nfe::all();
        return view('nfe.index', compact('nfes'));
    }

    public function transmit(Nfe $nfe)
    {
        try {
            $this->nfeService->transmit($nfe);
            return redirect()->back()->with('success', "NFe Transmitida! Status: {$nfe->status}");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function downloadPdf(Nfe $nfe, \App\Services\Fiscal\DanfeService $danfeService)
    {
        try {
            $pdfContent = $danfeService->generatePdf($nfe);
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, "nfe-{$nfe->numero}.pdf", ['Content-Type' => 'application/pdf']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function viewPdf(Nfe $nfe, \App\Services\Fiscal\DanfeService $danfeService)
    {
        try {
            $pdfContent = $danfeService->generatePdf($nfe);
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"nfe-{$nfe->numero}.pdf\""
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
