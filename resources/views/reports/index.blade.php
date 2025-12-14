@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1>Relatórios de Notas Fiscais</h1>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <form action="{{ route('reports.index') }}" method="GET" class="row gx-3 gy-2 align-items-end">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Data Inicial</label>
                        <input type="date" class="form-control" name="start_date" id="start_date"
                            value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Data Final</label>
                        <input type="date" class="form-control" name="end_date" id="end_date"
                            value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="">Todos</option>
                            <option value="authorized" {{ ($filters['status'] ?? '') == 'authorized' ? 'selected' : '' }}>
                                Autorizada</option>
                            <option value="canceled" {{ ($filters['status'] ?? '') == 'canceled' ? 'selected' : '' }}>
                                Cancelada</option>
                            <option value="rejected" {{ ($filters['status'] ?? '') == 'rejected' ? 'selected' : '' }}>
                                Rejeitada</option>
                            <option value="created" {{ ($filters['status'] ?? '') == 'created' ? 'selected' : '' }}>Criada
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="customer_id" class="form-label">Cliente</label>
                        <select class="form-select" name="customer_id" id="customer_id">
                            <option value="">Todos</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                                    {{ Str::limit($customer->razao_social, 20) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Button -->
        <div class="row mb-3">
            <div class="col-md-12 text-end">
                <form action="{{ route('reports.export') }}" method="GET" target="_blank" class="d-inline">
                    <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                    <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                    <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                    <input type="hidden" name="customer_id" value="{{ $filters['customer_id'] ?? '' }}">
                    <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar
                        CSV</button>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="card">
            <div class="card-body">
                @if($nfes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Número/Série</th>
                                    <th>Emissão</th>
                                    <th>Emissor</th>
                                    <th>Cliente</th>
                                    <th>Valor Total</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nfes as $nfe)
                                    <tr>
                                        <td>{{ $nfe->id }}</td>
                                        <td>{{ $nfe->numero }} / {{ $nfe->serie }}</td>
                                        <td>{{ $nfe->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $nfe->company->razao_social ?? '-' }}</td>
                                        <td>{{ $nfe->customer->razao_social ?? '-' }}</td>
                                        <td>R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge 
                                                        @if($nfe->status == 'authorized') bg-success 
                                                        @elseif($nfe->status == 'canceled') bg-danger 
                                                        @elseif($nfe->status == 'rejected') bg-warning text-dark
                                                        @else bg-secondary @endif">
                                                {{ ucfirst($nfe->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('nfe.view', $nfe->id) }}" class="btn btn-sm btn-info"
                                                title="Visualizar"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $nfes->links() }}
                    </div>
                @else
                    <div class="alert alert-info text-center">Nenhum registro encontrado para os filtros selecionados.</div>
                @endif
            </div>
        </div>
    </div>
@endsection