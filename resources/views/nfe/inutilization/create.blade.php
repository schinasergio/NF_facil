<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inutilização de Numeração de NFe') }}
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

                    <form action="{{ route('nfe.inutilization.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="serie" value="Série" />
                                <x-text-input id="serie" class="block mt-1 w-full" type="number" name="serie"
                                    :value="old('serie', 1)" required />
                                <x-input-error :messages="$errors->get('serie')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="numero_inicial" value="Número Inicial" />
                                <x-text-input id="numero_inicial" class="block mt-1 w-full" type="number"
                                    name="numero_inicial" :value="old('numero_inicial')" required />
                                <x-input-error :messages="$errors->get('numero_inicial')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="numero_final" value="Número Final" />
                                <x-text-input id="numero_final" class="block mt-1 w-full" type="number"
                                    name="numero_final" :value="old('numero_final')" required />
                                <x-input-error :messages="$errors->get('numero_final')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="justificativa" value="Justificativa (mín. 15 caracteres)" />
                            <textarea id="justificativa" name="justificativa"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3" required minlength="15">{{ old('justificativa') }}</textarea>
                            <x-input-error :messages="$errors->get('justificativa')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Solicitar Inutilização') }}
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