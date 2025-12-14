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
        // Get customers belonging to companies owned by the authenticated user
        $customers = Customer::whereHas('company', function ($query) {
            $query->where('user_id', auth()->id());
        })->get();
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
    public function store(\App\Http\Requests\StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        $user = \Illuminate\Support\Facades\Auth::user();
        $company = $user->companies()->firstOrFail();

        $addressData = $request->only(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais']);
        $customerData = $request->except(['logradouro', 'numero', 'complemento', 'bairro', 'cep', 'cidade', 'uf', 'pais', '_token']);
        $customerData['company_id'] = $company->id;

        $this->customerService->createCustomer($customerData, $addressData);

        return redirect()->route('customers.index')->with('success', 'Destinatário cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\StoreCustomerRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $validated = $request->validated();
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
        $this->authorize('delete', $customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Destinatário removido.');
    }
}
