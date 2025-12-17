<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notas Fiscais Emitidas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between mb-6">
                        <div class="flex space-x-4">
                             <a href="{{ route('nfe.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Nova NFe
                            </a>
                            <a href="{{ route('nfe.inutilization.create') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Inutilizar Numeração
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($nfes as $nfe)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">#{{ $nfe->numero }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">R$ {{ $nfe->valor_total }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($nfe->status === 'authorized') bg-green-100 text-green-800 
                                                @elseif($nfe->status === 'rejected') bg-red-100 text-red-800
                                                @elseif($nfe->status === 'canceled') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $nfe->status }}
                                            </span>
                                            @if($nfe->mensagem_sefaz)
                                                <br><small class="text-gray-500">{{ Str::limit($nfe->mensagem_sefaz, 30) }}</small>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($nfe->status === 'signed' || $nfe->status === 'rejected')
                                                <form action="{{ route('nfe.transmit', $nfe) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 mr-2">Transmitir</button>
                                                </form>
                                            @endif
                                            
                                            <a href="{{ route('nfe.pdf', $nfe) }}" class="text-gray-600 hover:text-gray-900 mr-2">PDF</a>
                                            <a href="{{ route('nfe.view', $nfe) }}" target="_blank" class="text-gray-600 hover:text-gray-900 mr-2">Ver</a>

                                            @if($nfe->status === 'authorized')
                                                <a href="{{ route('nfe.correction', $nfe) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">CC-e</a>
                                                <form action="{{ route('nfe.cancel', $nfe) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza?');">
                                                    @csrf
                                                    <input type="hidden" name="justificativa" value="Cancelamento solicitado pelo usuário via sistema web.">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Cancelar</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
