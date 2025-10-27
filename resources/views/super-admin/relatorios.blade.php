@extends('super-admin.layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Relatórios')
@section('page-title', 'Relatórios e Analytics')
@section('page-subtitle', 'Estatísticas detalhadas do sistema')

@section('content')
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Receita Total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">
                    R$ {{ number_format($receitaTotal, 2, ',', '.') }}
                </p>
            </div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-money-bill-wave text-xl"></i>
            </span>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Top Empresas</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $topEmpresas->count() }}</p>
            </div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                <i class="fa-solid fa-trophy text-xl"></i>
            </span>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        @php
            $totalEmpresas = \App\Models\User::where('tipo', 'empresa')->count();
            $media = $totalEmpresas > 0 ? $receitaTotal / $totalEmpresas : 0;
        @endphp
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Média por Empresa</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">
                    R$ {{ number_format($media, 2, ',', '.') }}
                </p>
            </div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                <i class="fa-solid fa-chart-bar text-xl"></i>
            </span>
        </div>
    </article>
</section>

<section class="mt-6 grid gap-6 xl:grid-cols-3">
    <article class="xl:col-span-2 rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <header class="mb-4 flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            Receita por mês (últimos 12 meses)
        </header>
        <div class="overflow-hidden">
            <canvas id="chartReceita" class="w-full"></canvas>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <header class="mb-4 flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-yellow-100 text-yellow-600">
                <i class="fa-solid fa-trophy"></i>
            </span>
            Top 10 - Mensagens enviadas
        </header>
        <div class="overflow-hidden rounded-2xl border border-slate-200/70">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left font-semibold text-slate-500">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Empresa</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                        @forelse ($topEmpresas as $index => $empresa)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3">
                                    @if ($index === 0)
                                        <span class="text-amber-500">ðŸ¥‡</span>
                                    @elseif ($index === 1)
                                        <span class="text-slate-400">ðŸ¥ˆ</span>
                                    @elseif ($index === 2)
                                        <span class="text-amber-700">ðŸ¥‰</span>
                                    @else
                                        <span class="text-sm font-semibold text-slate-500">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ Str::limit($empresa->name, 24) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span
                                        class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                                        {{ number_format($empresa->total_mensagens ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">
                                    Nenhum dado disponível
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </article>
</section>

<section class="mt-6 rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <header class="mb-4 flex items-center gap-3 text-lg font-semibold text-slate-800">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
            <i class="fa-solid fa-table"></i>
        </span>
        Empresas - visão detalhada
    </header>

    <div class="overflow-hidden rounded-2xl border border-slate-200/70">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left font-semibold text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Empresa</th>
                        <th class="px-4 py-3">Plano</th>
                        <th class="px-4 py-3 text-right">Mensagens (mês)</th>
                        <th class="px-4 py-3 text-right">Mensagens (total)</th>
                        <th class="px-4 py-3 text-right">Receita</th>
                        <th class="px-4 py-3">Status</th>
                       <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse ($todasEmpresas as $empresa)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $empresa->name }}</div>
                                <div class="text-xs text-slate-500">{{ $empresa->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                                    {{ strtoupper($empresa->plano ?? '-') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                {{ number_format($empresa->mensagens_mes ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                {{ number_format($empresa->total_mensagens ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-800">
                                @if ($empresa->valor_pago)
                                    <div class="font-semibold">
                                        R$ {{ number_format($empresa->valor_pago, 2, ',', '.') }}
                                    </div>
                                    @if ($empresa->data_ultimo_pagamento)
                                        <div class="text-xs text-slate-500">
                                            {{ $empresa->data_ultimo_pagamento->format('d/m/Y') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
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
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('super-admin.empresas.detalhes', $empresa->id) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-indigo-200 text-indigo-600 transition hover:bg-indigo-50">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                <i class="fa-solid fa-inbox mb-3 text-3xl text-slate-300"></i>
                                <div>Nenhuma empresa cadastrada</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 text-sm font-semibold text-slate-600">
                    <tr>
                        <td colspan="2" class="px-4 py-3 uppercase tracking-wide">Total</td>
                        <td class="px-4 py-3 text-right">
                            {{ number_format($todasEmpresas->sum('mensagens_mes'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ number_format($todasEmpresas->sum('total_mensagens'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            R$ {{ number_format($todasEmpresas->sum('valor_pago'), 2, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    const receitaCanvas = document.getElementById('chartReceita');
    if (receitaCanvas) {
        const ctx = receitaCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($receitaPorMes->pluck('mes')->toArray()) !!},
                datasets: [{
                    label: 'Receita (R$)',
                    data: {!! json_encode($receitaPorMes->pluck('total')->toArray()) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                let label = context.dataset.label ? context.dataset.label + ': ' : '';
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
                            callback(value) {
                                return 'R$ ' + Number(value).toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection


