<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\Cadastros\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::all();
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreCompanyRequest $request)
    {
        $validated = $request->validated();

        // Split data into company and address arrays
        $addressData = $request->only(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais']);
        $companyData = $request->except(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais', '_token']);

        $this->companyService->createCompany($companyData, $addressData);

        return redirect()->route('companies.index')->with('success', 'Empresa cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\StoreCompanyRequest $request, Company $company)
    {
        $validated = $request->validated();
        $addressData = $request->only(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais']);
        $companyData = $request->except(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais', '_method', '_token']);

        $this->companyService->updateCompany($company, $companyData, $addressData);

        return redirect()->route('companies.index')->with('success', 'Empresa atualizada!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Empresa removida.');
    }
}
