<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Empresa') }}
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

                    <!-- Alpine.js Logic for Masks -->
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
                        <form action="{{ route('companies.update', $company->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-medium mb-4">Dados da Empresa</h3>
                                    <div class="mb-4">
                                        <x-input-label for="razao_social" value="Razão Social" />
                                        <x-text-input id="razao_social" class="block mt-1 w-full" type="text"
                                            name="razao_social" :value="old('razao_social', $company->razao_social)"
                                            required autofocus />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="nome_fantasia" value="Fantasia" />
                                        <x-text-input id="nome_fantasia" class="block mt-1 w-full" type="text"
                                            name="nome_fantasia" :value="old('nome_fantasia', $company->nome_fantasia)" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="cnpj" value="CNPJ" />
                                        <x-text-input id="cnpj" class="block mt-1 w-full" type="text" name="cnpj"
                                            :value="old('cnpj', $company->cnpj)" required x-on:input="maskCNPJ"
                                            placeholder="00.000.000/0000-00" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="ie" value="Inscrição Estadual" />
                                        <x-text-input id="ie" class="block mt-1 w-full" type="text" name="ie"
                                            :value="old('ie', $company->ie)" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="regime_tributario" value="Regime Tributário" />
                                        <select id="regime_tributario" name="regime_tributario"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            required>
                                            <option value="">Selecione...</option>
                                            <option value="1" {{ old('regime_tributario', $company->regime_tributario) == '1' ? 'selected' : '' }}>Simples Nacional
                                            </option>
                                            <option value="3" {{ old('regime_tributario', $company->regime_tributario) == '3' ? 'selected' : '' }}>Regime Normal
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Environment Toggle -->
                                    <div class="mb-4 p-4 rounded-lg border-2"
                                        x-data="{ env: '{{ old('ambiente', $company->ambiente ?? 2) }}' }"
                                        :class="env == '1' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'">

                                        <x-input-label for="ambiente" value="Ambiente de Emissão" class="font-bold" />

                                        <div class="mt-2 flex gap-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="ambiente" value="2" x-model="env"
                                                    class="form-radio text-green-600">
                                                <span class="ml-2 font-medium text-green-700">Homologação
                                                    (Testes)</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="ambiente" value="1" x-model="env"
                                                    class="form-radio text-red-600">
                                                <span class="ml-2 font-medium text-red-700">Produção (Valendo!)</span>
                                            </label>
                                        </div>

                                        <div x-show="env == '1'"
                                            class="mt-2 text-sm text-red-600 font-bold animate-pulse">
                                            ⚠️ CUIDADO: Notas emitidas neste ambiente têm validade fiscal!
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="email" value="Email" />
                                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                            :value="old('email', $company->email)" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="telefone" value="Telefone" />
                                        <x-text-input id="telefone" class="block mt-1 w-full" type="text"
                                            name="telefone" :value="old('telefone', $company->telefone)"
                                            x-on:input="maskPhone" placeholder="(99) 99999-9999" />
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-medium mb-4">Endereço</h3>
                                    <div class="mb-4">
                                        <x-input-label for="logradouro" value="Logradouro" />
                                        <x-text-input id="logradouro" class="block mt-1 w-full" type="text"
                                            name="logradouro" :value="old('logradouro', $company->address->logradouro ?? '')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="numero" value="Número" />
                                        <x-text-input id="numero" class="block mt-1 w-full" type="text" name="numero"
                                            :value="old('numero', $company->address->numero ?? '')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="complemento" value="Complemento" />
                                        <x-text-input id="complemento" class="block mt-1 w-full" type="text"
                                            name="complemento" :value="old('complemento', $company->address->complemento ?? '')" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="bairro" value="Bairro" />
                                        <x-text-input id="bairro" class="block mt-1 w-full" type="text" name="bairro"
                                            :value="old('bairro', $company->address->bairro ?? '')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="cep" value="CEP" />
                                        <x-text-input id="cep" class="block mt-1 w-full" type="text" name="cep"
                                            :value="old('cep', $company->address->cep ?? '')" required
                                            x-on:input="maskCEP" placeholder="00000-000" />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="cidade" value="Cidade" />
                                        <x-text-input id="cidade" class="block mt-1 w-full" type="text" name="cidade"
                                            :value="old('cidade', $company->address->cidade ?? '')" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="uf" value="UF" />
                                        <x-text-input id="uf" class="block mt-1 w-full" type="text" name="uf"
                                            :value="old('uf', $company->address->uf ?? '')" required />
                                    </div>
                                    <!-- Hidden Country -->
                                    <input type="hidden" name="pais" value="Brasil">
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-2">
                                <a href="{{ route('companies.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Voltar') }}
                                </a>
                                <x-primary-button>
                                    {{ __('Atualizar') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>