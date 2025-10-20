@extends('super-admin.layouts.app')

@section('title', 'Editar - ' . $empresa->name)
@section('page-title', 'Editar Empresa')
@section('page-subtitle', $empresa->name)

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="stat-card">
            <form action="{{ route('super-admin.empresas.atualizar', $empresa->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Nome -->
                    <div class="col-md-6">
                        <label class="form-label">Nome da Empresa *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $empresa->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $empresa->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- WhatsApp -->
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsapp_number" class="form-control"
                               value="{{ old('whatsapp_number', $empresa->whatsapp_number) }}"
                               placeholder="55 51 99999-9999">
                    </div>

                    <!-- Plano -->
                    <div class="col-md-6">
                        <label class="form-label">Plano *</label>
                        <select name="plano" class="form-select @error('plano') is-invalid @enderror" required>
                            <option value="trial" {{ old('plano', $empresa->plano) === 'trial' ? 'selected' : '' }}>Trial (Teste)</option>
                            <option value="monthly" {{ old('plano', $empresa->plano) === 'monthly' ? 'selected' : '' }}>Plano Mensal</option>
                            <option value="quarterly" {{ old('plano', $empresa->plano) === 'quarterly' ? 'selected' : '' }}>Plano Trimestral</option>
                            <option value="semiannual" {{ old('plano', $empresa->plano) === 'semiannual' ? 'selected' : '' }}>Plano Semestral</option>
                            <option value="annual" {{ old('plano', $empresa->plano) === 'annual' ? 'selected' : '' }}>Plano Anual</option>
                        </select>
                        @error('plano')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status de Acesso -->
                    <div class="col-md-6">
                        <label class="form-label">Status de Acesso</label>
                        <select name="acesso_ativo" class="form-select">
                            <option value="1" {{ old('acesso_ativo', $empresa->acesso_ativo) ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ !old('acesso_ativo', $empresa->acesso_ativo) ? 'selected' : '' }}>Bloqueado</option>
                        </select>
                    </div>

                    <!-- Acesso Liberado Até -->
                    <div class="col-md-6">
                        <label class="form-label">Acesso Liberado Até</label>
                        <input type="datetime-local" name="acesso_liberado_ate" class="form-control"
                               value="{{ old('acesso_liberado_ate', $empresa->acesso_liberado_ate?->format('Y-m-d\TH:i')) }}">
                        <small class="text-muted">Deixe em branco para acesso ilimitado</small>
                    </div>

                    <!-- Limite de Mensagens -->
                    <div class="col-md-6">
                        <label class="form-label">Limite de mensagens/mês *</label>
                        <input type="number" name="limite_requisicoes_mes"
                               class="form-control @error('limite_requisicoes_mes') is-invalid @enderror"
                               value="{{ old('limite_requisicoes_mes', $empresa->limite_requisicoes_mes) }}"
                               min="0" required>
                        @error('limite_requisicoes_mes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Valor Pago -->
                    <div class="col-md-6">
                        <label class="form-label">Valor Pago (R$)</label>
                        <input type="number" name="valor_pago" class="form-control" step="0.01"
                               value="{{ old('valor_pago', $empresa->valor_pago) }}"
                               placeholder="0.00">
                    </div>

                    <!-- Observações -->
                    <div class="col-12">
                        <label class="form-label">Observações do Admin</label>
                        <textarea name="observacoes_admin" class="form-control" rows="4"
                                  placeholder="Notas internas sobre esta empresa...">{{ old('observacoes_admin', $empresa->observacoes_admin) }}</textarea>
                    </div>

                    <!-- Informações de Controle -->
                    <div class="col-12">
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> Informações:</strong><br>
                            <small>
                                • Mensagens enviadas no mês atual: <strong>{{ $empresa->requisicoes_mes_atual }}</strong><br>
                                • Total de mensagens enviadas: <strong>{{ number_format($empresa->total_requisicoes, 0, ',', '.') }}</strong><br>
                                • Cadastrado em: <strong>{{ $empresa->created_at->format('d/m/Y H:i') }}</strong>
                            </small>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                               class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
