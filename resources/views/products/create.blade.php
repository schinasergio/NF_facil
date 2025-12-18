<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Novo Produto') }}
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

                    <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="nome" value="Nome do Produto" />
                                <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                    :value="old('nome')" required autofocus />
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="codigo_sku" value="SKU (Código)" />
                                <x-text-input id="codigo_sku" class="block mt-1 w-full" type="text" name="codigo_sku"
                                    :value="old('codigo_sku')" required />
                                <x-input-error :messages="$errors->get('codigo_sku')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="ncm" value="NCM (8 dígitos)" />
                                <x-text-input id="ncm" class="block mt-1 w-full" type="text" name="ncm"
                                    :value="old('ncm')" required x-data
                                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 8)"
                                    placeholder="Apenas números" />
                                <x-input-error :messages="$errors->get('ncm')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="cest" value="CEST (Opcional)" />
                                <x-text-input id="cest" class="block mt-1 w-full" type="text" name="cest"
                                    :value="old('cest')" x-data
                                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 7)"
                                    placeholder="Apenas números" />
                                <x-input-error :messages="$errors->get('cest')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="unidade" value="Unidade (ex: UN, KG)" />
                                <x-text-input id="unidade" class="block mt-1 w-full" type="text" name="unidade"
                                    :value="old('unidade', 'UN')" required />
                                <x-input-error :messages="$errors->get('unidade')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="preco_venda" value="Preço de Venda (R$)" />
                                <x-text-input id="preco_venda" class="block mt-1 w-full" type="text" name="preco_venda"
                                    :value="old('preco_venda')" required x-data
                                    x-on:input="$el.value = $el.value.replace(/[^0-9.,]/g, '')" placeholder="0.00" />
                                <x-input-error :messages="$errors->get('preco_venda')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="origem" value="Origem" />
                                <select id="origem" name="origem"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="0" {{ old('origem') == '0' ? 'selected' : '' }}>0 - Nacional</option>
                                    <option value="1" {{ old('origem') == '1' ? 'selected' : '' }}>1 - Estrangeira (Imp.
                                        Direta)</option>
                                    <option value="2" {{ old('origem') == '2' ? 'selected' : '' }}>2 - Estrangeira (Merc.
                                        Interno)</option>
                                </select>
                                <x-input-error :messages="$errors->get('origem')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Salvar Produto') }}
                            </x-primary-button>
                            <a href="{{ route('products.index') }}"
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