<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Service; // Future
use App\Services\Fiscal\NfseService;
use Illuminate\Http\Request;

class NfseController extends Controller
{
    protected $nfseService;

    public function __construct(NfseService $nfseService)
    {
        $this->nfseService = $nfseService;
        $this->middleware('auth');
    }

    public function create()
    {
        // Get user's active company (or first one)
        $company = auth()->user()->companies()->first();

        if (!$company) {
            return redirect()->route('companies.create')->with('error', 'Cadastre uma empresa primeiro.');
        }

        return view('nfse.create', compact('company'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tomador_nome' => 'required|string',
            'tomador_cnpj' => 'required|string',
            'valor_servico' => 'required',
        ]);

        $company = auth()->user()->companies()->first();

        try {
            $response = $this->nfseService->emitir($company, $request->all());

            $msg = $response['message'] ?? 'Processado';

            return redirect()->route('dashboard')->with('success', "NFS-e: $msg");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao emitir NFS-e: ' . $e->getMessage());
        }
    }
}
