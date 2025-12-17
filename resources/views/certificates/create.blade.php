<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Carregar Certificado Digital') }} - {{ $company->razao_social }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">Ops!</strong>
                            <span class="block sm:inline">Verifique os erros abaixo:</span>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('companies.certificate.store', $company) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="pfx_file" value="Arquivo PFX" />
                            <input id="pfx_file"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                type="file" name="pfx_file" accept=".pfx" required>
                            <p class="mt-1 text-sm text-gray-500" id="pfx_file_help">Selecione o arquivo .pfx do seu
                                certificado A1.</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password" value="Senha do Certificado" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                required />
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-2">
                            <a href="{{ route('companies.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Voltar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Carregar e Validar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>