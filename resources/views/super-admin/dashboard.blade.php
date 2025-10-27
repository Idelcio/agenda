@extends('super-admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Visão geral do sistema')

@section('content')
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Total de Empresas</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $totalEmpresas }}</p>
            </div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                <i class="fa-solid fa-building text-xl"></i>
            </span>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Empresas Ativas</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $empresasAtivas }}</p>
            </div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-check-circle text-xl"></i>
            </span>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Acessos Vencidos</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $empresasVencidas }}</p>
            </div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                <i class="fa-solid fa-exclamation-triangle text-xl"></i>
            </span>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Mensagens enviadas (mês)</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">
                    {{ number_format($totalMensagensMes, 0, ',', '.') }}
                </p>
            </div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                <i class="fa-solid fa-paper-plane text-xl"></i>
            </span>
        </div>
    </article>
</section>

<section class="mt-6 grid gap-6 lg:grid-cols-2">
    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <header class="mb-4 flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                <i class="fa-solid fa-chart-pie"></i>
            </span>
            Empresas por Plano
        </header>
        <div class="overflow-hidden">
            <canvas id="chartPlanos" class="w-full"></canvas>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <header class="mb-4 flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            Mensagens enviadas (últimos 6 meses)
        </header>
        <div class="overflow-hidden">
            <canvas id="chartMensagens" class="w-full"></canvas>
        </div>
    </article>
</section>

@if ($mensagensPorEmpresa->isNotEmpty())
<section class="mt-6 rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <header class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-envelope-open-text"></i>
            </span>
            Mensagens enviadas por empresa (mês atual)
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="h-80">
            <canvas id="chartMensagensEmpresas" class="h-full w-full"></canvas>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200/70">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left font-semibold text-slate-500">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Empresa</th>
                            <th class="px-4 py-3 text-right">Mensagens (mês)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                        @foreach ($mensagensPorEmpresa as $index => $empresa)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-sm text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium">{{ $empresa->name }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
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
</section>
@endif

<section class="mt-6 rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <header class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                <i class="fa-solid fa-clock"></i>
            </span>
            Empresas Recentes
        </div>
        <a href="{{ route('super-admin.empresas') }}"
            class="inline-flex items-center gap-2 self-start rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-600/40 transition hover:bg-indigo-500">
            Ver todas
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </header>

    <div class="overflow-hidden rounded-2xl border border-slate-200/70">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left font-semibold text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Empresa</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Plano</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Cadastro</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse ($empresasRecentes as $empresa)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $empresa->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $empresa->email }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700">
                                    {{ strtoupper($empresa->plano) }}
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
                            <td class="px-4 py-3 text-slate-600">{{ $empresa->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-indigo-200 text-indigo-600 transition hover:bg-indigo-50">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                Nenhuma empresa cadastrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    const ctxPlanosCanvas = document.getElementById('chartPlanos');
    if (ctxPlanosCanvas) {
        const ctxPlanos = ctxPlanosCanvas.getContext('2d');
        new Chart(ctxPlanos, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($empresasPorPlano->pluck('plano')->map(fn ($p) => strtoupper($p))->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($empresasPorPlano->pluck('total')->toArray()) !!},
                    backgroundColor: ['#4f46e5', '#6366f1', '#a855f7', '#ec4899', '#f97316'],
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
    }

    const ctxMensagensCanvas = document.getElementById('chartMensagens');
    if (ctxMensagensCanvas) {
        const ctxMensagens = ctxMensagensCanvas.getContext('2d');
        new Chart(ctxMensagens, {
            type: 'line',
            data: {
                labels: {!! json_encode($mensagensUltimosSeisMeses->pluck('mes')->toArray()) !!},
                datasets: [{
                    label: 'Mensagens enviadas',
                    data: {!! json_encode($mensagensUltimosSeisMeses->pluck('total')->toArray()) !!},
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.08)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    const ctxMensagensEmpresasCanvas = document.getElementById('chartMensagensEmpresas');
    if (ctxMensagensEmpresasCanvas) {
        const ctxMensagensEmpresas = ctxMensagensEmpresasCanvas.getContext('2d');
        new Chart(ctxMensagensEmpresas, {
            type: 'bar',
            data: {
                labels: {!! json_encode($mensagensPorEmpresa->take(12)->pluck('name')->toArray()) !!},
                datasets: [{
                    label: 'Mensagens enviadas',
                    data: {!! json_encode($mensagensPorEmpresa->take(12)->pluck('total')->toArray()) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 8
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
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }
</script>
@endsection


