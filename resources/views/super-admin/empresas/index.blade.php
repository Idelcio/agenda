@extends('super-admin.layouts.app')

@section('title', 'Gerenciar Empresas')
@section('page-title', 'Empresas')
@section('page-subtitle', 'Gerenciamento de empresas cadastradas')

@section('content')
<!-- Filtros -->
<div class="stat-card mb-4">
    <form method="GET" action="{{ route('super-admin.empresas') }}">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" name="busca" class="form-control"
                       placeholder="Nome, email ou WhatsApp..."
                       value="{{ request('busca') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ativas" {{ request('status') === 'ativas' ? 'selected' : '' }}>Ativas</option>
                    <option value="vencidas" {{ request('status') === 'vencidas' ? 'selected' : '' }}>Vencidas</option>
                    <option value="bloqueadas" {{ request('status') === 'bloqueadas' ? 'selected' : '' }}>Bloqueadas</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Plano</label>
                <select name="plano" class="form-select">
                    <option value="">Todos</option>
                    <option value="trial" {{ request('plano') === 'trial' ? 'selected' : '' }}>Trial (Teste)</option>
                    <option value="monthly" {{ request('plano') === 'monthly' ? 'selected' : '' }}>Plano Mensal</option>
                    <option value="quarterly" {{ request('plano') === 'quarterly' ? 'selected' : '' }}>Plano Trimestral</option>
                    <option value="semiannual" {{ request('plano') === 'semiannual' ? 'selected' : '' }}>Plano Semestral</option>
                    <option value="annual" {{ request('plano') === 'annual' ? 'selected' : '' }}>Plano Anual</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Tabela de Empresas -->
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Contato</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th>Acesso Ate</th>
                    <th>Mensagens</th>
                    <th class="text-center">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empresas as $empresa)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $empresa->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $empresa->appointments_count }} compromissos &bull; {{ $empresa->clientes_count }} clientes
                                </small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <i class="fas fa-envelope text-muted"></i> {{ $empresa->email }}
                                <br>
                                @if($empresa->whatsapp_number)
                                    <i class="fas fa-phone text-success"></i> {{ $empresa->whatsapp_number }}
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $planoNomes = [
                                    'trial' => 'Trial',
                                    'monthly' => 'Mensal',
                                    'quarterly' => 'Trimestral',
                                    'semiannual' => 'Semestral',
                                    'annual' => 'Anual',
                                ];
                                $planoNome = $planoNomes[$empresa->plano] ?? strtoupper($empresa->plano);
                            @endphp
                            <span class="badge bg-info">{{ $planoNome }}</span>
                        </td>
                        <td>
                            @if($empresa->acesso_ativo && (!$empresa->acesso_liberado_ate || $empresa->acesso_liberado_ate >= now()))
                                <span class="badge-status ativo">
                                    <i class="fas fa-check-circle"></i> Ativo
                                </span>
                            @elseif($empresa->acesso_liberado_ate && $empresa->acesso_liberado_ate < now())
                                <span class="badge-status vencido">
                                    <i class="fas fa-exclamation-triangle"></i> Vencido
                                </span>
                            @else
                                <span class="badge-status bloqueado">
                                    <i class="fas fa-ban"></i> Bloqueado
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($empresa->acesso_liberado_ate)
                                {{ $empresa->acesso_liberado_ate->format('d/m/Y') }}
                                <br>
                                <small class="text-muted">
                                    {{ $empresa->acesso_liberado_ate->diffForHumans() }}
                                </small>
                            @else
                                <span class="text-muted">Ilimitado</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">
                                {{ number_format($empresa->total_mensagens ?? 0, 0, ',', '.') }} mensagens
                            </div>
                            <div class="small text-muted">
                                Mes atual: {{ number_format($empresa->mensagens_mes ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                                   class="btn btn-outline-primary" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.empresas.editar', $empresa->id) }}"
                                   class="btn btn-outline-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            Nenhuma empresa encontrada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($empresas->hasPages())
        <div class="mt-3">
            {{ $empresas->links() }}
        </div>
    @endif
</div>
@endsection
