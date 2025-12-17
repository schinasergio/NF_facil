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
                                        :value="old('cnpj')" required />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="ie" value="Inscrição Estadual" />
                                    <x-text-input id="ie" class="block mt-1 w-full" type="text" name="ie"
                                        :value="old('ie')" />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="regime_tributario"
                                        value="Regime Tributário (1=Simples, 3=Normal)" />
                                    <x-text-input id="regime_tributario" class="block mt-1 w-full" type="number"
                                        name="regime_tributario" :value="old('regime_tributario')" required />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                        :value="old('email')" required />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="telefone" value="Telefone" />
                                    <x-text-input id="telefone" class="block mt-1 w-full" type="text" name="telefone"
                                        :value="old('telefone')" />
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
                                        :value="old('cep')" required />
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
</x-app-layout>