<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Cliente') }}
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

                    <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Dados do Cliente -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados do Cliente</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="razao_social" value="Razão Social / Nome" />
                                    <x-text-input id="razao_social" class="block mt-1 w-full" type="text"
                                        name="razao_social" :value="old('razao_social', $customer->razao_social)"
                                        required autofocus />
                                </div>
                                <div>
                                    <x-input-label for="nome_fantasia" value="Nome Fantasia" />
                                    <x-text-input id="nome_fantasia" class="block mt-1 w-full" type="text"
                                        name="nome_fantasia" :value="old('nome_fantasia', $customer->nome_fantasia)" />
                                </div>
                                <div>
                                    <x-input-label for="cpf_cnpj" value="CPF / CNPJ" />
                                    <x-text-input id="cpf_cnpj" class="block mt-1 w-full" type="text" name="cpf_cnpj"
                                        :value="old('cpf_cnpj', $customer->cpf_cnpj)" required />
                                </div>
                                <div>
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                        :value="old('email', $customer->email)" required />
                                </div>
                                <div>
                                    <x-input-label for="telefone" value="Telefone" />
                                    <x-text-input id="telefone" class="block mt-1 w-full" type="text" name="telefone"
                                        :value="old('telefone', $customer->telefone)" />
                                </div>
                                <div>
                                    <x-input-label for="ie" value="Inscrição Estadual" />
                                    <x-text-input id="ie" class="block mt-1 w-full" type="text" name="ie"
                                        :value="old('ie', $customer->ie)" />
                                </div>
                                <div>
                                    <x-input-label for="indicador_ie" value="Indicador IE" />
                                    <select id="indicador_ie" name="indicador_ie"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="1" {{ old('indicador_ie', $customer->indicador_ie) == '1' ? 'selected' : '' }}>1 - Contribuinte ICMS</option>
                                        <option value="2" {{ old('indicador_ie', $customer->indicador_ie) == '2' ? 'selected' : '' }}>2 - Contribuinte Isento</option>
                                        <option value="9" {{ old('indicador_ie', $customer->indicador_ie) == '9' ? 'selected' : '' }}>9 - Não Contribuinte</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Endereço</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="logradouro" value="Logradouro" />
                                    <x-text-input id="logradouro" class="block mt-1 w-full" type="text"
                                        name="logradouro" :value="old('logradouro', $customer->logradouro)" required />
                                </div>
                                <div>
                                    <x-input-label for="numero" value="Número" />
                                    <x-text-input id="numero" class="block mt-1 w-full" type="text" name="numero"
                                        :value="old('numero', $customer->numero)" required />
                                </div>
                                <div>
                                    <x-input-label for="complemento" value="Complemento" />
                                    <x-text-input id="complemento" class="block mt-1 w-full" type="text"
                                        name="complemento" :value="old('complemento', $customer->complemento)" />
                                </div>
                                <div>
                                    <x-input-label for="bairro" value="Bairro" />
                                    <x-text-input id="bairro" class="block mt-1 w-full" type="text" name="bairro"
                                        :value="old('bairro', $customer->bairro)" required />
                                </div>
                                <div>
                                    <x-input-label for="cep" value="CEP" />
                                    <x-text-input id="cep" class="block mt-1 w-full" type="text" name="cep"
                                        :value="old('cep', $customer->cep)" required />
                                </div>
                                <div>
                                    <x-input-label for="cidade" value="Cidade" />
                                    <x-text-input id="cidade" class="block mt-1 w-full" type="text" name="cidade"
                                        :value="old('cidade', $customer->cidade)" required />
                                </div>
                                <div>
                                    <x-input-label for="uf" value="UF" />
                                    <x-text-input id="uf" class="block mt-1 w-full" type="text" name="uf"
                                        :value="old('uf', $customer->uf)" maxlength="2" required />
                                </div>
                                <div>
                                    <x-input-label for="pais" value="País" />
                                    <x-text-input id="pais" class="block mt-1 w-full bg-gray-100" type="text"
                                        name="pais" value="Brasil" readonly />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Atualizar Cliente') }}
                            </x-primary-button>
                            <a href="{{ route('customers.index') }}"
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