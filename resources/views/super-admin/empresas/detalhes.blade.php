@extends('super-admin.layouts.app')

@section('title', 'Detalhes - ' . $empresa->name)
@section('page-title', $empresa->name)
@section('page-subtitle', 'Detalhes e estatísticas da empresa')

@section('content')
<!-- Botões de Ação Rápida -->
<div class="mb-4">
    <div class="btn-group">
        <a href="{{ route('super-admin.empresas.editar', $empresa->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>

        <form action="{{ route('super-admin.empresas.toggle-acesso', $empresa->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-{{ $empresa->acesso_ativo ? 'warning' : 'success' }}">
                <i class="fas fa-{{ $empresa->acesso_ativo ? 'ban' : 'check' }}"></i>
                {{ $empresa->acesso_ativo ? 'Bloquear' : 'Liberar' }} Acesso
            </button>
        </form>

        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalTrial">
            <i class="fas fa-gift"></i> Liberar Trial
        </button>

        <form action="{{ route('super-admin.empresas.resetar-requisicoes', $empresa->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-secondary"
                    onclick="return confirm('Resetar contador de requisições?')">
                <i class="fas fa-redo"></i> Resetar Requisições
            </button>
        </form>

        <form action="{{ route('super-admin.empresas.deletar', $empresa->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Tem certeza? Esta ação não pode ser desfeita!')">
                <i class="fas fa-trash"></i> Deletar
            </button>
        </form>
    </div>

    <a href="{{ route('super-admin.empresas') }}" class="btn btn-outline-secondary float-end">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<!-- Informações Gerais -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="label">Compromissos</div>
            <div class="value">{{ $totalCompromissos }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card success">
            <div class="label">Confirmados</div>
            <div class="value">{{ $compromissosConfirmados }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="label">Cancelados</div>
            <div class="value">{{ $compromissosCancelados }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="label">Mensagens (30d)</div>
            <div class="value">{{ $mensagensUltimos30Dias }}</div>
        </div>
    </div>
</div>

<!-- Detalhes e Gráfico -->
<div class="row g-4">
    <!-- Informações Detalhadas -->
    <div class="col-md-6">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-info-circle text-primary"></i> Informações
            </h5>

            <table class="table table-sm">
                <tr>
                    <td width="40%"><strong>Email:</strong></td>
                    <td>{{ $empresa->email }}</td>
                </tr>
                <tr>
                    <td><strong>WhatsApp:</strong></td>
                    <td>{{ $empresa->whatsapp_number ?? 'Não informado' }}</td>
                </tr>
                <tr>
                    <td><strong>Plano:</strong></td>
                    <td><span class="badge bg-info">{{ strtoupper($empresa->plano) }}</span></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        @if($empresa->acesso_ativo && (!$empresa->acesso_liberado_ate || $empresa->acesso_liberado_ate >= now()))
                            <span class="badge-status ativo">Ativo</span>
                        @elseif($empresa->acesso_liberado_ate && $empresa->acesso_liberado_ate < now())
                            <span class="badge-status vencido">Vencido</span>
                        @else
                            <span class="badge-status bloqueado">Bloqueado</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Acesso Até:</strong></td>
                    <td>
                        {{ $empresa->acesso_liberado_ate ? $empresa->acesso_liberado_ate->format('d/m/Y H:i') : 'Ilimitado' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Cadastro:</strong></td>
                    <td>{{ $empresa->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Total de Clientes:</strong></td>
                    <td>{{ $totalClientes }}</td>
                </tr>
                <tr>
                    <td><strong>Mensagens enviadas (mês):</strong></td>
                    <td>{{ number_format($mensagensMesAtual, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Total de mensagens enviadas:</strong></td>
                    <td>{{ number_format($totalMensagens, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Mensagens (últimos 30 dias):</strong></td>
                    <td>{{ number_format($mensagensUltimos30Dias, 0, ',', '.') }}</td>
                </tr>
                @if($empresa->valor_pago)
                    <tr>
                        <td><strong>Último Pagamento:</strong></td>
                        <td>
                            R$ {{ number_format($empresa->valor_pago, 2, ',', '.') }}
                            @if($empresa->data_ultimo_pagamento)
                                <br>
                                <small class="text-muted">{{ $empresa->data_ultimo_pagamento->format('d/m/Y') }}</small>
                            @endif
                        </td>
                    </tr>
                @endif
            </table>

            @if($empresa->observacoes_admin)
                <div class="alert alert-info mt-3">
                    <strong>Observações:</strong><br>
                    {{ $empresa->observacoes_admin }}
                </div>
            @endif
        </div>
    </div>

    <!-- Gráfico de Mensagens -->
    <div class="col-md-6">
        <div class="stat-card">
            <h5 class="mb-3">
                <i class="fas fa-chart-area text-success"></i> Mensagens enviadas (últimos 30 dias)
            </h5>
            <canvas id="chartMensagensEmpresa"></canvas>
        </div>
    </div>
</div>

<!-- Modal Trial -->
<div class="modal fade" id="modalTrial" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('super-admin.empresas.trial', $empresa->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-gift"></i> Liberar Acesso Trial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Quantos dias de trial?</label>
                        <input type="number" name="dias" class="form-control" value="3" min="1" max="90" required>
                        <small class="text-muted">Informe o número de dias de acesso gratuito</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Liberar Trial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Gráfico de mensagens enviadas
    const ctx = document.getElementById('chartMensagensEmpresa').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($mensagensPorDia->pluck('dia')->map(fn($d) => date('d/m', strtotime($d)))->toArray()) !!},
            datasets: [{
                label: 'Mensagens enviadas',
                data: {!! json_encode($mensagensPorDia->pluck('total')->toArray()) !!},
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: '#667eea',
                borderWidth: 1
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
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endsection
