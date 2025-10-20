@extends('super-admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Visão geral do sistema')

@section('content')
<!-- Estatísticas Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Total de Empresas</div>
                    <div class="value">{{ $totalEmpresas }}</div>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Empresas Ativas</div>
                    <div class="value">{{ $empresasAtivas }}</div>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Acessos Vencidos</div>
                    <div class="value">{{ $empresasVencidas }}</div>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Mensagens enviadas (mês)</div>
                    <div class="value">{{ number_format($totalMensagensMes, 0, ',', '.') }}</div>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-4 mb-4">
    <!-- Gráfico de Empresas por Plano -->
    <div class="col-md-6">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-chart-pie text-primary"></i> Empresas por Plano
            </h5>
            <canvas id="chartPlanos"></canvas>
        </div>
    </div>

    <!-- Gráfico de Mensagens por mês -->
    <div class="col-md-6">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-chart-line text-success"></i> Mensagens enviadas (últimos 6 meses)
            </h5>
            <canvas id="chartMensagens"></canvas>
        </div>
    </div>
</div>

@if($mensagensPorEmpresa->isNotEmpty())
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-envelope-open-text text-success"></i> Mensagens enviadas por empresa (mês atual)
                </h5>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div style="min-height: 320px;">
                        <canvas id="chartMensagensEmpresas"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="text-muted">#</th>
                                    <th>Empresa</th>
                                    <th class="text-end">Mensagens (mês)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mensagensPorEmpresa as $index => $empresa)
                                    <tr>
                                        <td class="text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $empresa->name }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-success">
                                                {{ number_format($empresa->total, 0, ',', '.') }}
                                            </span>
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
</div>
@endif

<!-- Empresas Recentes -->
<div class="row">
    <div class="col-12">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-clock text-info"></i> Empresas Recentes
                </h5>
                <a href="{{ route('super-admin.empresas') }}" class="btn btn-sm btn-outline-primary">
                    Ver Todas <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Email</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($empresasRecentes as $empresa)
                            <tr>
                                <td>
                                    <strong>{{ $empresa->name }}</strong>
                                </td>
                                <td>{{ $empresa->email }}</td>
                                <td>
                                    <span class="badge bg-info">{{ strtoupper($empresa->plano) }}</span>
                                </td>
                                <td>
                                    @if($empresa->acesso_ativo && (!$empresa->acesso_liberado_ate || $empresa->acesso_liberado_ate >= now()))
                                        <span class="badge-status ativo">Ativo</span>
                                    @elseif($empresa->acesso_liberado_ate && $empresa->acesso_liberado_ate < now())
                                        <span class="badge-status vencido">Vencido</span>
                                    @else
                                        <span class="badge-status bloqueado">Bloqueado</span>
                                    @endif
                                </td>
                                <td>{{ $empresa->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhuma empresa cadastrada
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Gráfico de Empresas por Plano
    const ctxPlanos = document.getElementById('chartPlanos').getContext('2d');
    new Chart(ctxPlanos, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($empresasPorPlano->pluck('plano')->map(fn($p) => strtoupper($p))->toArray()) !!},
            datasets: [{
                data: {!! json_encode($empresasPorPlano->pluck('total')->toArray()) !!},
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c',
                    '#fa709a'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfico de Mensagens por mês
    const ctxMensagens = document.getElementById('chartMensagens').getContext('2d');
    new Chart(ctxMensagens, {
        type: 'line',
        data: {
            labels: {!! json_encode($mensagensUltimosSeisMeses->pluck('mes')->toArray()) !!},
            datasets: [{
                label: 'Mensagens enviadas',
                data: {!! json_encode($mensagensUltimosSeisMeses->pluck('total')->toArray()) !!},
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    if (document.getElementById('chartMensagensEmpresas')) {
        const ctxMensagensEmpresas = document.getElementById('chartMensagensEmpresas').getContext('2d');
        new Chart(ctxMensagensEmpresas, {
            type: 'bar',
            data: {
                labels: {!! json_encode($mensagensPorEmpresa->take(12)->pluck('name')->toArray()) !!},
                datasets: [{
                    label: 'Mensagens enviadas',
                    data: {!! json_encode($mensagensPorEmpresa->take(12)->pluck('total')->toArray()) !!},
                    backgroundColor: 'rgba(37, 211, 102, 0.6)',
                    borderColor: '#25D366',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
</script>
@endsection
