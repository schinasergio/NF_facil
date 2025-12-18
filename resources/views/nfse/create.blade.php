<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nova NFS-e (Nota Fiscal de Serviço)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                        <p class="font-bold">Em construção</p>
                        <p>O módulo de NFS-e está sendo configurado para Santos e São Paulo.</p>
                    </div>

                    <form action="{{ route('nfse.store') }}" method="POST">
                        @csrf

                        <!-- Tomador / Cliente -->
                        <h3 class="text-lg font-medium mb-2 mt-4">Tomador do Serviço</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="tomador_cnpj" value="CPF/CNPJ" />
                                <x-text-input id="tomador_cnpj" class="block mt-1 w-full" type="text"
                                    name="tomador_cnpj" required />
                            </div>
                            <div>
                                <x-input-label for="tomador_nome" value="Razão Social / Nome" />
                                <x-text-input id="tomador_nome" class="block mt-1 w-full" type="text"
                                    name="tomador_nome" required />
                            </div>
                        </div>

                        <!-- Serviço -->
                        <h3 class="text-lg font-medium mb-2 mt-6">Detalhes do Serviço</h3>
                        <div class="mb-4">
                            <x-input-label for="discriminacao" value="Discriminação do Serviço" />
                            <textarea id="discriminacao" name="discriminacao" rows="3"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="valor_servico" value="Valor do Serviço (R$)" />
                                <x-text-input id="valor_servico" class="block mt-1 w-full" type="text"
                                    name="valor_servico" placeholder="0,00" />
                            </div>
                            <div>
                                <x-input-label for="iss_retido" value="ISS Retido?" />
                                <select id="iss_retido" name="iss_retido"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button class="ml-4">
                                {{ __('Emitir NFS-e (Simulação)') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>