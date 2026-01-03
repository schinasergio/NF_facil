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
                            <a href="{{ route('nfe.create') }}"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Nova NFe
                            </a>
                            <a href="{{ route('nfe.inutilization.create') }}"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Inutilizar Numeração
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Número</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($nfes as $nfe)
                                    <tbody x-data="{ open: false }" class="border-b border-gray-200">
                                        <tr class="hover:bg-gray-50 cursor-pointer" @click="open = !open">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <button class="mr-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                                        <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                        <svg x-show="open" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor" style="display: none;">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                    <span class="font-bold">#{{ $nfe->numero }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">R$
                                                {{ number_format($nfe->valor_total, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @if($nfe->status === 'authorized') bg-green-100 text-green-800 
                                                            @elseif($nfe->status === 'rejected') bg-red-100 text-red-800
                                                            @elseif($nfe->status === 'canceled') bg-red-100 text-red-800
                                                            @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ $nfe->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right" @click.stop>
                                                <!-- Main Actions (Keep visible) -->
                                                @if($nfe->status === 'signed' || $nfe->status === 'rejected')
                                                    <form action="{{ route('nfe.transmit', $nfe) }}" method="POST"
                                                        class="inline-block">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-blue-600 hover:text-blue-900 mr-2">Transmitir</button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('nfe.view', $nfe) }}" target="_blank"
                                                    class="text-gray-600 hover:text-gray-900 mr-2">Ver Danfe</a>
                                                @if($nfe->status === 'authorized')
                                                    <form action="{{ route('nfe.cancel', $nfe) }}" method="POST"
                                                        class="inline-block" onsubmit="return confirm('Tem certeza?');">
                                                        @csrf
                                                        <input type="hidden" name="justificativa" value="Cancelamento web">
                                                        <button type="submit"
                                                            class="text-red-600 hover:text-red-900">Cancelar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        <!-- Detail Row -->
                                        <tr x-show="open" x-transition class="bg-gray-50">
                                            <td colspan="4" class="px-6 py-4">
                                                <div class="mb-4">
                                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Controle de Status da NF
                                                    </h3>

                                                    <!-- Actions Bar -->
                                                    <div class="flex space-x-3 mb-4">
                                                        <a href="{{ route('nfe.xml', $nfe) }}"
                                                            class="inline-flex items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md text-sm font-medium transition cursor-pointer"
                                                            @click.stop>
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                                </path>
                                                            </svg>
                                                            Download XML
                                                        </a>
                                                        <a href="{{ route('nfe.pdf', $nfe) }}"
                                                            class="inline-flex items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md text-sm font-medium transition cursor-pointer"
                                                            @click.stop>
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            Download PDF
                                                        </a>
                                                        @if($nfe->status === 'authorized')
                                                            <a href="{{ route('nfe.correction', $nfe) }}"
                                                                class="inline-flex items-center px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 rounded-md text-sm font-medium transition"
                                                                @click.stop>
                                                                Carta de Correção
                                                            </a>
                                                        @endif
                                                    </div>

                                                    <!-- Logs Table -->
                                                    <div
                                                        class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                                        <table class="min-w-full divide-y divide-gray-300">
                                                            <thead class="bg-gray-100">
                                                                <tr>
                                                                    <th scope="col"
                                                                        class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                                        Status</th>
                                                                    <th scope="col"
                                                                        class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">
                                                                        Protocolo</th>
                                                                    <th scope="col"
                                                                        class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">
                                                                        Mensagem/Resposta</th>
                                                                    <th scope="col"
                                                                        class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">
                                                                        Data</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-200 bg-white">
                                                                @forelse($nfe->logs->sortByDesc('created_at') as $log)
                                                                    <tr>
                                                                        <td
                                                                            class="whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900">
                                                                            {{ $log->status }}</td>
                                                                        <td
                                                                            class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                                                            {{ $log->protocolo ?? '-' }}</td>
                                                                        <td class="px-3 py-2 text-sm text-gray-500">
                                                                            {{ Str::limit($log->message, 100) }}</td>
                                                                        <td
                                                                            class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                                                            {{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="4"
                                                                            class="px-3 py-2 text-sm text-center text-gray-500 text-xs italic">
                                                                            Nenhum histórico registrado ainda.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>