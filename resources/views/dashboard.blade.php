<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Quick Actions -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('nfe.create') }}"
                    class="block p-6 bg-blue-600 rounded-lg text-white hover:bg-blue-700 transition shadow-lg flex items-center justify-between">
                    <div>
                        <div class="text-lg font-bold">Emitir NFe (Produto)</div>
                        <div class="text-blue-100 text-sm">Venda de Mercadorias (Modelo 55)</div>
                    </div>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </a>

                <a href="{{ route('nfse.create') }}"
                    class="block p-6 bg-purple-600 rounded-lg text-white hover:bg-purple-700 transition shadow-lg flex items-center justify-between">
                    <div>
                        <div class="text-lg font-bold">Emitir NFS-e (Serviço)</div>
                        <div class="text-purple-100 text-sm">Prestação de Serviços (Municipal)</div>
                    </div>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <!-- Authorized -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-sm font-medium text-gray-500">Notas Autorizadas</div>
                        <div class="mt-1 text-3xl font-semibold text-green-600">{{ $authorizedCount }}</div>
                    </div>
                </div>

                <!-- Canceled -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-sm font-medium text-gray-500">Notas Canceladas</div>
                        <div class="mt-1 text-3xl font-semibold text-red-600">{{ $canceledCount }}</div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-sm font-medium text-gray-500">Pendentes/Rejeitadas</div>
                        <div class="mt-1 text-3xl font-semibold text-yellow-600">{{ $pendingCount }}</div>
                    </div>
                </div>

                <!-- Volume -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-sm font-medium text-gray-500">Volume Mensal</div>
                        <div class="mt-1 text-3xl font-semibold text-blue-600">R$
                            {{ number_format($monthlyVolume, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Atividade Recente</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Número</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Destinatário</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentNfes as $nfe)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $nfe->numero }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $nfe->customer->razao_social ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$
                                            {{ number_format($nfe->valor_total, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $nfe->status === 'authorized' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $nfe->status === 'canceled' ? 'bg-red-100 text-red-800' : '' }}
                                                        {{ $nfe->status === 'rejected' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                        {{ $nfe->status === 'created' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                {{ ucfirst($nfe->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $nfe->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhuma
                                            nota fiscal emitida recentemente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>