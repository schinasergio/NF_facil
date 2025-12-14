@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1>Dashboard</h1>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Authorized Card -->
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Autorizadas</div>
                    <div class="card-body">
                        <h5 class="card-title display-4">{{ $authorizedCount }}</h5>
                        <p class="card-text">Notas emitidas com sucesso.</p>
                    </div>
                </div>
            </div>

            <!-- Canceled Card -->
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Canceladas</div>
                    <div class="card-body">
                        <h5 class="card-title display-4">{{ $canceledCount }}</h5>
                        <p class="card-text">Notas canceladas.</p>
                    </div>
                </div>
            </div>

            <!-- Pending Card -->
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Pendentes/Rejeitadas</div>
                    <div class="card-body">
                        <h5 class="card-title display-4">{{ $pendingCount }}</h5>
                        <p class="card-text">Aguardando ação.</p>
                    </div>
                </div>
            </div>

            <!-- Volume Card -->
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Volume (R$)</div>
                    <div class="card-body">
                        <h5 class="card-title display-4">R$ {{ number_format($monthlyVolume, 2, ',', '.') }}</h5>
                        <p class="card-text">Total autorizado.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Últimas Atividades
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Série/Número</th>
                                    <th>Emissor</th>
                                    <th>Destinatário</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentNfes as $nfe)
                                    <tr>
                                        <td>{{ $nfe->id }}</td>
                                        <td>{{ $nfe->serie }} / {{ $nfe->numero }}</td>
                                        <td>{{ $nfe->company->razao_social ?? 'N/A' }}</td>
                                        <td>{{ $nfe->customer->razao_social ?? 'N/A' }}</td>
                                        <td>R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge 
                                                    @if($nfe->status == 'authorized') bg-success 
                                                    @elseif($nfe->status == 'canceled') bg-danger 
                                                    @else bg-secondary @endif">
                                                {{ ucfirst($nfe->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $nfe->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('nfe.view', $nfe->id) }}" class="btn btn-sm btn-info">Ver</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Nenhuma atividade recente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection