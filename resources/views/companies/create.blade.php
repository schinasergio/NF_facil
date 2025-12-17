<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nova Empresa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Alpine.js Logic -->
                    <div x-data="{
                        maskCNPJ(e) {
                            let v = e.target.value.replace(/\D/g, '');
                            if (v.length > 14) v = v.slice(0, 14);
                            if (v.length > 12) {
                                v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
                            } else if (v.length > 8) {
                                v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4}).*/, '$1.$2.$3/$4');
                            } else if (v.length > 5) {
                                v = v.replace(/^(\d{2})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
                            } else if (v.length > 2) {
                                v = v.replace(/^(\d{2})(\d{0,3}).*/, '$1.$2');
                            }
                            e.target.value = v;
                        },
                        maskPhone(e) {
                            let v = e.target.value.replace(/\D/g, '');
                            if (v.length > 11) v = v.slice(0, 11);
                            if (v.length > 10) {
                                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                            } else if (v.length > 6) {
                                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                            } else if (v.length > 2) {
                                v = v.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
                            } else {
                                if (v.length > 0) v = v.replace(/^(\d*)/, '($1');
                            }
                            e.target.value = v;
                        },
                        maskCEP(e) {
                            let v = e.target.value.replace(/\D/g, '');
                            if (v.length > 8) v = v.slice(0, 8);
                            if (v.length > 5) {
                                v = v.replace(/^(\d{5})(\d{0,3}).*/, '$1-$2');
                            }
                            e.target.value = v;
                        }
                    }">
                        <form action="{{ route('companies.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-medium mb-4">Dados da Empresa</h3>
                                    <div class="mb-4">
                                        <x-input-label for="razao_social" value="Razão Social" />
                                        <x-text-input id="razao_social" class="block mt-1 w-full" type="text"
                                            name="razao_social" :value="old('razao_social')" required autofocus />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="nome_fantasia" value="Fantasia" />
                                        <x-text-input id="nome_fantasia" class="block mt-1 w-full" type="text"
                                            name="nome_fantasia" :value="old('nome_fantasia')" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="cnpj" value="CNPJ" />
                                        <x-text-input id="cnpj" class="block mt-1 w-full" type="text" name="cnpj"
                                            :value="old('cnpj')" required x-on:input="maskCNPJ"
                                            placeholder="00.000.000/0000-00" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="ie" value="Inscrição Estadual" />
                                        <x-text-input id="ie" class="block mt-1 w-full" type="text" name="ie"
                                            :value="old('ie')" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="regime_tributario" value="Regime Tributário" />
                                        <select id="regime_tributario" name="regime_tributario"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            required>
                                            <option value="">Selecione...</option>
                                            <option value="1" {{ old('regime_tributario') == '1' ? 'selected' : '' }}>
                                                Simples Nacional</option>
                                            <option value="3" {{ old('regime_tributario') == '3' ? 'selected' : '' }}>
                                                Regime Normal</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="email" value="Email" />
                                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                            :value="old('email')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="telefone" value="Telefone" />
                                        <x-text-input id="telefone" class="block mt-1 w-full" type="text"
                                            name="telefone" :value="old('telefone')" x-on:input="maskPhone"
                                            placeholder="(99) 99999-9999" />
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-medium mb-4">Endereço</h3>
                                    <div class="mb-4">
                                        <x-input-label for="logradouro" value="Logradouro" />
                                        <x-text-input id="logradouro" class="block mt-1 w-full" type="text"
                                            name="logradouro" :value="old('logradouro')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="numero" value="Número" />
                                        <x-text-input id="numero" class="block mt-1 w-full" type="text" name="numero"
                                            :value="old('numero')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="complemento" value="Complemento" />
                                        <x-text-input id="complemento" class="block mt-1 w-full" type="text"
                                            name="complemento" :value="old('complemento')" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="bairro" value="Bairro" />
                                        <x-text-input id="bairro" class="block mt-1 w-full" type="text" name="bairro"
                                            :value="old('bairro')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="cep" value="CEP" />
                                        <x-text-input id="cep" class="block mt-1 w-full" type="text" name="cep"
                                            :value="old('cep')" required x-on:input="maskCEP" placeholder="00000-000" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="cidade" value="Cidade" />
                                        <x-text-input id="cidade" class="block mt-1 w-full" type="text" name="cidade"
                                            :value="old('cidade')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="uf" value="UF" />
                                        <x-text-input id="uf" class="block mt-1 w-full" type="text" name="uf"
                                            :value="old('uf')" required />
                                    </div>
                                    <!-- Hidden Country -->
                                    <input type="hidden" name="pais" value="Brasil">
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <x-primary-button class="ml-4">
                                    {{ __('Salvar') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>