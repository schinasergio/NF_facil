<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Relatórios de Notas Fiscais') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Filtros</h3>
                    <form action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <x-input-label for="start_date" value="Data Inicial" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="$filters['start_date'] ?? ''" />
                        </div>
                        <div>
                            <x-input-label for="end_date" value="Data Final" />
                            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="$filters['end_date'] ?? ''" />
                        </div>
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select name="status" id="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Todos</option>
                                <option value="authorized" {{ ($filters['status'] ?? '') == 'authorized' ? 'selected' : '' }}>Autorizada</option>
                                <option value="canceled" {{ ($filters['status'] ?? '') == 'canceled' ? 'selected' : '' }}>Cancelada</option>
                                <option value="rejected" {{ ($filters['status'] ?? '') == 'rejected' ? 'selected' : '' }}>Rejeitada</option>
                                <option value="created" {{ ($filters['status'] ?? '') == 'created' ? 'selected' : '' }}>Criada</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="customer_id" value="Cliente" />
                            <select name="customer_id" id="customer_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Todos</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                                        {{ Str::limit($customer->razao_social, 20) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="md:col-span-4 flex justify-end space-x-2 mt-4">
                            <x-primary-button>
                                {{ __('Filtrar') }}
                            </x-primary-button>
                            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Limpar
                            </a>
                            <a href="{{ route('reports.export', $filters) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Exportar CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($nfes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número/Série</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emissão</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($nfes as $nfe)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $nfe->numero }} / {{ $nfe->serie }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $nfe->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $nfe->customer->razao_social ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($nfe->status == 'authorized') bg-green-100 text-green-800 
                                                    @elseif($nfe->status == 'canceled') bg-red-100 text-red-800 
                                                    @elseif($nfe->status == 'rejected') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($nfe->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('nfe.view', $nfe->id) }}" class="text-indigo-600 hover:text-indigo-900">Visualizar</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $nfes->links() }}
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500">
                            Nenhum registro encontrado para os filtros selecionados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
