<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Emitir Nova Nota Fiscal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                            role="alert">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('nfe.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="company_id" value="Emitente" />
                                <select id="company_id" name="company_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                    required>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->razao_social }}
                                            ({{ $company->cnpj }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('company_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="customer_id" value="Destinatário" />
                                <select id="customer_id" name="customer_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                    required>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->razao_social }}
                                            ({{ $customer->cpf_cnpj }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Selecionar Produtos</h3>

                            @if($products->isEmpty())
                                <p class="text-gray-500">Nenhum produto cadastrado.</p>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($products as $product)
                                        <div class="flex items-center">
                                            <input id="product_{{ $product->id }}" type="checkbox" name="items[][product_id]"
                                                value="{{ $product->id }}"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <label for="product_{{ $product->id }}" class="ml-2 text-sm text-gray-700">
                                                {{ $product->nome }} <span class="text-gray-500">(R$
                                                    {{ number_format($product->preco_venda, 2, ',', '.') }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Gerar e Assinar XML') }}
                            </x-primary-button>
                            <a href="{{ route('nfe.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>