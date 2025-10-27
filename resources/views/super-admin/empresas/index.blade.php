@extends('super-admin.layouts.app')

@section('title', 'Gerenciar Empresas')
@section('page-title', 'Empresas')
@section('page-subtitle', 'Gerenciamento de empresas cadastradas')

@section('content')
<section class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <form method="GET" action="{{ route('super-admin.empresas') }}" class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Buscar</span>
                <input type="text" name="busca" value="{{ request('busca') }}"
                    class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    placeholder="Nome, email ou WhatsApp..." />
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Status</span>
                <select name="status"
                    class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Todos</option>
                    <option value="ativas" @selected(request('status') === 'ativas')>Ativas</option>
                    <option value="vencidas" @selected(request('status') === 'vencidas')>Vencidas</option>
                    <option value="bloqueadas" @selected(request('status') === 'bloqueadas')>Bloqueadas</option>
                </select>
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-600">Plano</span>
                <select name="plano"
                    class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Todos</option>
                    <option value="trial" @selected(request('plano') === 'trial')>Trial (Teste)</option>
                    <option value="monthly" @selected(request('plano') === 'monthly')>Plano Mensal</option>
                    <option value="quarterly" @selected(request('plano') === 'quarterly')>Plano Trimestral</option>
                    <option value="semiannual" @selected(request('plano') === 'semiannual')>Plano Semestral</option>
                    <option value="annual" @selected(request('plano') === 'annual')>Plano Anual</option>
                </select>
            </label>

            <div class="flex items-end">
                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500">
                    <i class="fa-solid fa-search text-xs"></i>
                    Filtrar
                </button>
            </div>
        </div>
    </form>
</section>

<section class="mt-6 rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <div class="overflow-hidden rounded-2xl border border-slate-200/70">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left font-semibold text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Empresa</th>
                        <th class="px-4 py-3">Contato</th>
                        <th class="px-4 py-3">Plano</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Acesso até</th>
                        <th class="px-4 py-3">Mensagens</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse ($empresas as $empresa)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $empresa->name }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $empresa->appointments_count }} compromissos &bull; {{ $empresa->clientes_count }} clientes
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i class="fa-solid fa-envelope text-sm text-slate-400"></i>
                                    <span>{{ $empresa->email }}</span>
                                </div>
                                @if ($empresa->whatsapp_number)
                                    <div class="mt-1 flex items-center gap-2 text-slate-600">
                                        <i class="fa-solid fa-phone text-sm text-emerald-500"></i>
                                        <span>{{ $empresa->whatsapp_number }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
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
                                <span
                                    class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700">
                                    {{ $planoNome }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($empresa->acesso_ativo && (!$empresa->acesso_liberado_ate || $empresa->acesso_liberado_ate >= now()))
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                        <i class="fa-solid fa-check-circle text-emerald-500"></i>
                                        Ativo
                                    </span>
                                @elseif ($empresa->acesso_liberado_ate && $empresa->acesso_liberado_ate < now())
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                                        <i class="fa-solid fa-exclamation-triangle text-amber-500"></i>
                                        Vencido
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                                        <i class="fa-solid fa-ban text-slate-500"></i>
                                        Bloqueado
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if ($empresa->acesso_liberado_ate)
                                    <div class="font-semibold text-slate-800">
                                        {{ $empresa->acesso_liberado_ate->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $empresa->acesso_liberado_ate->diffForHumans() }}
                                    </div>
                                @else
                                    <span class="text-slate-400">Ilimitado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">
                                    {{ number_format($empresa->total_mensagens ?? 0, 0, ',', '.') }} mensagens
                                </div>
                                <div class="text-xs text-slate-500">
                                    Mês atual: {{ number_format($empresa->mensagens_mes ?? 0, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-indigo-200 text-indigo-600 transition hover:bg-indigo-50"
                                        title="Ver detalhes">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('super-admin.empresas.editar', $empresa->id) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-50"
                                        title="Editar">
                                        <i class="fa-solid fa-edit text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                <i class="fa-solid fa-inbox mb-3 text-3xl text-slate-300"></i>
                                <div>Nenhuma empresa encontrada</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($empresas->hasPages())
        <div class="mt-6">
            {{ $empresas->links() }}
        </div>
    @endif
</section>
@endsection


