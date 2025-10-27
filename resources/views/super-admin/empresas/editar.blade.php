@extends('super-admin.layouts.app')

@section('title', 'Editar - ' . $empresa->name)
@section('page-title', 'Editar Empresa')
@section('page-subtitle', $empresa->name)

@section('content')
@php
    $inputBase =
        'block w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $selectBase = $inputBase . ' appearance-none';
@endphp

<section class="mx-auto max-w-5xl rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <form action="{{ route('super-admin.empresas.atualizar', $empresa->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 md:grid-cols-2">
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Nome da empresa *</span>
                <input type="text" name="name" value="{{ old('name', $empresa->name) }}" required
                    class="{{ $inputBase }} @error('name') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @enderror">
                @error('name')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Email *</span>
                <input type="email" name="email" value="{{ old('email', $empresa->email) }}" required
                    class="{{ $inputBase }} @error('email') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @enderror">
                @error('email')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">WhatsApp</span>
                <input type="text" name="whatsapp_number" placeholder="55 51 99999-9999"
                    value="{{ old('whatsapp_number', $empresa->whatsapp_number) }}" class="{{ $inputBase }}">
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Plano *</span>
                <select name="plano" required
                    class="{{ $selectBase }} @error('plano') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @enderror">
                    <option value="trial" @selected(old('plano', $empresa->plano) === 'trial')>Trial (Teste)</option>
                    <option value="monthly" @selected(old('plano', $empresa->plano) === 'monthly')>Plano Mensal</option>
                    <option value="quarterly" @selected(old('plano', $empresa->plano) === 'quarterly')>Plano Trimestral</option>
                    <option value="semiannual" @selected(old('plano', $empresa->plano) === 'semiannual')>Plano Semestral</option>
                    <option value="annual" @selected(old('plano', $empresa->plano) === 'annual')>Plano Anual</option>
                </select>
                @error('plano')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Status de acesso</span>
                <select name="acesso_ativo" class="{{ $selectBase }}">
                    <option value="1" @selected(old('acesso_ativo', $empresa->acesso_ativo))>Ativo</option>
                    <option value="0" @selected(!old('acesso_ativo', $empresa->acesso_ativo))>Bloqueado</option>
                </select>
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Acesso liberado até</span>
                <input type="datetime-local" name="acesso_liberado_ate"
                    value="{{ old('acesso_liberado_ate', $empresa->acesso_liberado_ate?->format('Y-m-d\TH:i')) }}"
                    class="{{ $inputBase }}">
                <span class="text-xs text-slate-500">Deixe em branco para acesso ilimitado.</span>
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Limite de mensagens/mês *</span>
                <input type="number" name="limite_requisicoes_mes" min="0"
                    value="{{ old('limite_requisicoes_mes', $empresa->limite_requisicoes_mes) }}" required
                    class="{{ $inputBase }} @error('limite_requisicoes_mes') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @enderror">
                @error('limite_requisicoes_mes')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Valor pago (R$)</span>
                <input type="number" step="0.01" name="valor_pago" placeholder="0.00"
                    value="{{ old('valor_pago', $empresa->valor_pago) }}" class="{{ $inputBase }}">
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Device ID (API Brasil)</span>
                <input type="text" name="apibrasil_device_id"
                    placeholder="4db434ed-222d-4a5f-b4e6-63d73e45aa50"
                    value="{{ old('apibrasil_device_id', $empresa->apibrasil_device_id) }}" class="{{ $inputBase }}">
                <span class="text-xs text-slate-500">Obrigatório para sincronizar mensagens via API Brasil.</span>
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Device Token (API Brasil)</span>
                <input type="text" name="apibrasil_device_token"
                    placeholder="14edb6cf-1398-41c7-9109-af9b584b40ab"
                    value="{{ old('apibrasil_device_token', $empresa->apibrasil_device_token) }}" class="{{ $inputBase }}">
                <span class="text-xs text-slate-500">Se vazio, o sistema copiará o Device ID automaticamente.</span>
            </label>
        </div>

        <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-600">Observações do admin</span>
            <textarea name="observacoes_admin" rows="4" placeholder="Notas internas sobre esta empresa..."
                class="{{ $inputBase }}">{{ old('observacoes_admin', $empresa->observacoes_admin) }}</textarea>
        </label>

        <div class="rounded-2xl border border-sky-200 bg-sky-50/70 p-4 text-sm text-slate-700">
            <p class="font-semibold uppercase tracking-wide text-sky-600">
                <i class="fa-solid fa-info-circle mr-2"></i>Informações
            </p>
            <ul class="mt-2 space-y-1">
                <li>â€¢ Mensagens enviadas no mês atual: <strong>{{ $empresa->requisicoes_mes_atual }}</strong></li>
                <li>â€¢ Total de mensagens enviadas: <strong>{{ number_format($empresa->total_requisicoes, 0, ',', '.') }}</strong></li>
                <li>â€¢ Cadastrado em: <strong>{{ $empresa->created_at->format('d/m/Y H:i') }}</strong></li>
            </ul>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
            <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Voltar
            </a>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500">
                <i class="fa-solid fa-save text-xs"></i>
                Salvar alterações
            </button>
        </div>
    </form>
</section>
@endsection


