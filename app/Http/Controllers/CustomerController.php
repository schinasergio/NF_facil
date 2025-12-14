<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\Cadastros\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'razao_social' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string|max:18|unique:customers,cpf_cnpj',
            'email' => 'nullable|email',
            'indicador_ie' => 'required|string',
            // Address
            'logradouro' => 'required|string',
            'numero' => 'required|string',
            'bairro' => 'required|string',
            'cep' => 'required|string|max:10',
            'cidade' => 'required|string',
            'uf' => 'required|string|max:2',
        ]);

        $addressData = $request->only(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais']);
        $customerData = $request->except(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais', '_token']);

        $this->customerService->createCustomer($customerData, $addressData);

        return redirect()->route('customers.index')->with('success', 'Destinatário cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // Add validation
        $addressData = $request->only(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais']);
        $customerData = $request->except(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais', '_token', '_method']);

        $this->customerService->updateCustomer($customer, $customerData, $addressData);

        return redirect()->route('customers.index')->with('success', 'Destinatário atualizado!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Destinatário removido.');
    }
}
