@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 fw-bold text-gray-800">Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-5 g-4">
        <!-- Authorized Card -->
        <div class="col-md-3">
            <div class="card h-100 overflow-hidden text-white bg-gradient-success border-0 shadow">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 opacity-75">Autorizadas</h6>
                    <h2 class="display-5 fw-bold mb-0">{{ $authorizedCount }}</h2>
                </div>
            </div>
        </div>

        <!-- Canceled Card -->
        <div class="col-md-3">
            <div class="card h-100 overflow-hidden text-white bg-gradient-danger border-0 shadow">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 opacity-75">Canceladas</h6>
                    <h2 class="display-5 fw-bold mb-0">{{ $canceledCount }}</h2>
                </div>
            </div>
        </div>

        <!-- Pending Card -->
        <div class="col-md-3">
            <div class="card h-100 overflow-hidden text-white bg-gradient-warning border-0 shadow">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 opacity-75">Pendentes</h6>
                    <h2 class="display-5 fw-bold mb-0">{{ $pendingCount }}</h2>
                </div>
            </div>
        </div>

        <!-- Volume Card -->
        <div class="col-md-3">
            <div class="card h-100 overflow-hidden text-white bg-gradient-primary border-0 shadow">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 opacity-75">Volume Total</h6>
                    <h2 class="display-6 fw-bold mb-0">R$ {{ number_format($monthlyVolume, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="row g-4 mb-4">
        <!-- Chart Section -->
        <div class="col-lg-12"> <!-- Full width for valid rendering logic first, or 8/4 split -->
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">Visão Geral de Status</h5>
                </div>
                <div class="card-body position-relative" style="height: 300px;">
                    <canvas id="nfeStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Últimas Emissões</h5>
                    <a href="{{ route('nfe.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver Todas</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Número</th>
                                    <th>Emissor</th>
                                    <th>Destinatário</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentNfes as $nfe)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">{{ $nfe->serie }}/{{ $nfe->numero }}</td>
                                        <td>{{ Str::limit($nfe->company->razao_social ?? 'N/A', 20) }}</td>
                                        <td>{{ Str::limit($nfe->customer->razao_social ?? 'N/A', 20) }}</td>
                                        <td class="fw-bold text-dark">R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}</td>
                                        <td>
                                            @if($nfe->status == 'authorized')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Autorizada</span>
                                            @elseif($nfe->status == 'canceled')
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">Cancelada</span>
                                            @elseif($nfe->status == 'rejected')
                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2">Rejeitada</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">{{ ucfirst($nfe->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $nfe->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('nfe.view', $nfe->id) }}" class="btn btn-sm btn-light text-primary fw-medium rounded-pill px-3">Detalhes</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">Ainda não há notas emitidas.</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('nfeStatusChart').getContext('2d');
        const nfeStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Autorizadas', 'Canceladas', 'Pendentes'],
                datasets: [{
                    label: '# de Notas',
                    data: [{{ $authorizedCount }}, {{ $canceledCount }}, {{ $pendingCount }}],
                    backgroundColor: [
                        '#10b981', // Success
                        '#ef4444', // Danger
                        '#f59e0b'  // Warning
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>
@endpush