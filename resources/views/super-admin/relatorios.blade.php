@extends('super-admin.layouts.app')

@section('title', 'Relatórios')
@section('page-title', 'Relatórios e Analytics')
@section('page-subtitle', 'Estatísticas detalhadas do sistema')

@section('content')
<!-- Receita Total -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Receita Total</div>
                    <div class="value">R$ {{ number_format($receitaTotal, 2, ',', '.') }}</div>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Top Empresas</div>
                    <div class="value">{{ $topEmpresas->count() }}</div>
                </div>
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="label">Média por Empresa</div>
                    <div class="value">
                        @php
                            $total = \App\Models\User::where('tipo', 'empresa')->count();
                            $media = $total > 0 ? $receitaTotal / $total : 0;
                        @endphp
                        R$ {{ number_format($media, 2, ',', '.') }}
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-4 mb-4">
    <!-- Receita por Mês -->
    <div class="col-md-8">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-chart-line text-success"></i> Receita por Mês (Últimos 12 Meses)
            </h5>
            <canvas id="chartReceita"></canvas>
        </div>
    </div>

    <!-- Top 10 Empresas -->
    <div class="col-md-4">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-trophy text-warning"></i> Top 10 - Requisições
            </h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Empresa</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topEmpresas as $index => $empresa)
                            <tr>
                                <td>
                                    @if($index === 0)
                                        🥇
                                    @elseif($index === 1)
                                        🥈
                                    @elseif($index === 2)
                                        🥉
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ Str::limit($empresa->name, 20) }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-primary">
                                        {{ number_format($empresa->total_requisicoes, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    Nenhum dado disponível
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tabela Detalhada de Empresas -->
<div class="row">
    <div class="col-12">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-table text-info"></i> Todas as Empresas - Estatísticas
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Plano</th>
                            <th class="text-end">Requisições/Mês</th>
                            <th class="text-end">Total Requisições</th>
                            <th class="text-end">Valor Pago</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $todasEmpresas = \App\Models\User::where('tipo', 'empresa')
                                ->orderBy('total_requisicoes', 'desc')
                                ->get();
                        @endphp

                        @forelse($todasEmpresas as $empresa)
                            <tr>
                                <td>
                                    <strong>{{ $empresa->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $empresa->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ strtoupper($empresa->plano) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $empresa->requisicoes_mes_atual }}</strong>
                                    / {{ $empresa->limite_requisicoes_mes }}
                                    <br>
                                    <small class="text-muted">
                                        @php
                                            $percentual = $empresa->limite_requisicoes_mes > 0
                                                ? ($empresa->requisicoes_mes_atual / $empresa->limite_requisicoes_mes) * 100
                                                : 0;
                                        @endphp
                                        ({{ number_format($percentual, 1) }}%)
                                    </small>
                                </td>
                                <td class="text-end">
                                    <strong>{{ number_format($empresa->total_requisicoes, 0, ',', '.') }}</strong>
                                </td>
                                <td class="text-end">
                                    @if($empresa->valor_pago)
                                        <strong>R$ {{ number_format($empresa->valor_pago, 2, ',', '.') }}</strong>
                                        @if($empresa->data_ultimo_pagamento)
                                            <br>
                                            <small class="text-muted">
                                                {{ $empresa->data_ultimo_pagamento->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
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
                                <td class="text-center">
                                    <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Nenhuma empresa cadastrada
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3"><strong>TOTAL</strong></td>
                            <td class="text-end">
                                <strong>{{ number_format($todasEmpresas->sum('total_requisicoes'), 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>R$ {{ number_format($todasEmpresas->sum('valor_pago'), 2, ',', '.') }}</strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Gráfico de Receita por Mês
    const ctx = document.getElementById('chartReceita').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($receitaPorMes->pluck('mes')->toArray()) !!},
            datasets: [{
                label: 'Receita (R$)',
                data: {!! json_encode($receitaPorMes->pluck('total')->toArray()) !!},
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: '#22c55e',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
