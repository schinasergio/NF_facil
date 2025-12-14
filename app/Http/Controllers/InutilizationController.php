<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\Fiscal\InutilizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InutilizationController extends Controller
{
    protected $service;

    public function __construct(InutilizationService $service)
    {
        $this->service = $service;
    }

    public function create()
    {
        return view('nfe.inutilization.create');
    }

    public function store(\App\Http\Requests\InutilizationRequest $request)
    {
        $request->validated();

        // Assuming single company for now, or user belongs to company
        $user = Auth::user();
        $company = Company::where('user_id', $user->id)->firstOrFail();

        $result = $this->service->inutilize(
            $company,
            $request->serie,
            $request->numero_inicial,
            $request->numero_final,
            $request->justificativa
        );

        if ($result['status'] === 'authorized') {
            return redirect()->route('nfe.index')->with('success', 'Inutilização homologada! Protocolo: ' . $result['protocolo']);
        }

        return back()->withErrors(['message' => 'Erro ao inutilizar: ' . $result['message']]);
    }
}
