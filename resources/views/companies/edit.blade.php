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
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                            role="alert">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('companies.update', $company->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="nome" value="Razão Social" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome"
                                :value="old('nome', $company->nome)" required autofocus />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="nome_fantasia" value="Nome Fantasia" />
                            <x-text-input id="nome_fantasia" class="block mt-1 w-full" type="text" name="nome_fantasia"
                                :value="old('nome_fantasia', $company->nome_fantasia)" />
                            <x-input-error :messages="$errors->get('nome_fantasia')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="cnpj" value="CNPJ" />
                            <x-text-input id="cnpj" class="block mt-1 w-full" type="text" name="cnpj"
                                :value="old('cnpj', $company->cnpj)" required />
                            <x-input-error :messages="$errors->get('cnpj')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ie" value="Inscrição Estadual" />
                            <x-text-input id="ie" class="block mt-1 w-full" type="text" name="ie" :value="old('ie', $company->ie)" />
                            <x-input-error :messages="$errors->get('ie')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Atualizar Empresa') }}
                            </x-primary-button>
                            <a href="{{ route('companies.index') }}"
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