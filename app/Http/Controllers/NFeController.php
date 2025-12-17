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
        $companyIds = Company::where('user_id', auth()->id())->pluck('id');
        $companies = Company::whereIn('id', $companyIds)->get();

        $customers = Customer::whereIn('company_id', $companyIds)->get();
        $products = Product::whereIn('company_id', $companyIds)->get();

        // dd('DEBUG: ALL QUERIES OK');

        return view('nfe.create', compact('companies', 'customers', 'products'));
    }

    public function show(Nfe $nfe)
    {
        $this->authorize('view', $nfe);
        return view('nfe.show', compact('nfe'));
    }

    /**
     * Generate NFe.
     */
    public function store(\App\Http\Requests\StoreNfeRequest $request)
    {
        $request->validated();

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
        $nfes = \App\Models\Nfe::whereHas('company', function ($query) {
            $query->where('user_id', auth()->id());
        })->get();
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
    public function cancel(Nfe $nfe, \App\Http\Requests\CancelNfeRequest $request)
    {
        $request->validated();

        try {
            $this->nfeService->cancel($nfe, $request->justification);
            return redirect()->route('nfe.index')->with('success', 'NFe cancelada com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function correction(Nfe $nfe)
    {
        return view('nfe.correction', compact('nfe'));
    }

    public function storeCorrection(Nfe $nfe, \App\Http\Requests\CorrectionRequest $request)
    {
        $request->validated();

        try {
            $this->nfeService->correction($nfe, $request->correction_text);
            return redirect()->route('nfe.index')->with('success', 'Carta de correção enviada com sucesso!');
        } catch (\Exception $e) {
            // Log::error($e ...);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
