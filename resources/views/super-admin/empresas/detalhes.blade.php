@extends('super-admin.layouts.app')

@section('title', 'Detalhes - ' . $empresa->name)
@section('page-title', $empresa->name)
@section('page-subtitle', 'Detalhes e Estatísticas da empresa')

@section('content')
<section class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('super-admin.empresas.editar', $empresa->id) }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500">
                <i class="fa-solid fa-edit text-xs"></i>
                Editar
            </a>

            <form action="{{ route('super-admin.empresas.toggle-acesso', $empresa->id) }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition {{ $empresa->acesso_ativo ? 'bg-amber-500 hover:bg-amber-400 shadow-amber-500/30' : 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/30' }}">
                    <i class="fa-solid fa-{{ $empresa->acesso_ativo ? 'ban' : 'check' }} text-xs"></i>
                    {{ $empresa->acesso_ativo ? 'Bloquear' : 'Liberar' }} acesso
                </button>
            </form>

            <button type="button" id="openTrialModal"
                class="inline-flex items-center gap-2 rounded-2xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:bg-sky-400">
                <i class="fa-solid fa-gift text-xs"></i>
                Liberar trial
            </button>

            <form action="{{ route('super-admin.empresas.acesso-total', $empresa->id) }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-500"
                    onclick="return confirm('Liberar acesso total por 1 ano e configurar credenciais WhatsApp?')">
                    <i class="fa-solid fa-unlock-alt text-xs"></i>
                    Acesso total (1 ano)
                </button>
            </form>

            <form action="{{ route('super-admin.empresas.resetar-requisicoes', $empresa->id) }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-lg shadow-slate-400/30 transition hover:bg-slate-300"
                    onclick="return confirm('Resetar contador de requisições?')">
                    <i class="fa-solid fa-redo text-xs"></i>
                    Resetar requisições
                </button>
            </form>

            <form action="{{ route('super-admin.empresas.deletar', $empresa->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-600/30 transition hover:bg-rose-500"
                    onclick="return confirm('Tem certeza? Esta ação não pode ser desfeita!')">
                    <i class="fa-solid fa-trash text-xs"></i>
                    Deletar
                </button>
            </form>
        </div>

        <a href="{{ route('super-admin.empresas') }}"
            class="inline-flex items-center gap-2 self-start rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Voltar
        </a>
    </div>
</section>

<section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-3xl bg-white p-5 shadow-xl ring-1 ring-indigo-100">
        <p class="text-sm font-medium text-indigo-500">Compromissos</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $totalCompromissos }}</p>
    </article>
    <article class="rounded-3xl bg-white p-5 shadow-xl ring-1 ring-emerald-100">
        <p class="text-sm font-medium text-emerald-500">Confirmados</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $compromissosConfirmados }}</p>
    </article>
    <article class="rounded-3xl bg-white p-5 shadow-xl ring-1 ring-rose-100">
        <p class="text-sm font-medium text-rose-500">Cancelados</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $compromissosCancelados }}</p>
    </article>
    <article class="rounded-3xl bg-white p-5 shadow-xl ring-1 ring-amber-100">
        <p class="text-sm font-medium text-amber-500">Mensagens (30 dias)</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $mensagensUltimos30Dias }}</p>
    </article>
</section>

<section class="mt-6 grid gap-6 lg:grid-cols-2">
    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <header class="mb-5 flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                <i class="fa-solid fa-info-circle"></i>
            </span>
            Informações
        </header>

        <dl class="space-y-4 text-sm text-slate-600">
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Email</dt>
                <dd class="text-base text-slate-900">{{ $empresa->email }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">WhatsApp</dt>
                <dd class="text-base text-slate-900">{{ $empresa->whatsapp_number ?? 'Não informado' }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Plano</dt>
                <dd>
                    <span
                        class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700">
                        {{ strtoupper($empresa->plano) }}
                    </span>
                </dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Status</dt>
                <dd>
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
                </dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Acesso até</dt>
                <dd class="text-base text-slate-900">
                    {{ $empresa->acesso_liberado_ate ? $empresa->acesso_liberado_ate->format('d/m/Y H:i') : 'Ilimitado' }}
                </dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Cadastro</dt>
                <dd class="text-base text-slate-900">{{ $empresa->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Total de clientes</dt>
                <dd class="text-base text-slate-900">{{ $totalClientes }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Mensagens (mês)</dt>
                <dd class="text-base text-slate-900">{{ number_format($mensagensMesAtual, 0, ',', '.') }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Mensagens (total)</dt>
                <dd class="text-base text-slate-900">{{ number_format($totalMensagens, 0, ',', '.') }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="font-semibold text-slate-500">Mensagens (últimos 30 dias)</dt>
                <dd class="text-base text-slate-900">{{ number_format($mensagensUltimos30Dias, 0, ',', '.') }}</dd>
            </div>
            @if ($empresa->valor_pago)
                <div class="flex flex-col gap-1">
                    <dt class="font-semibold text-slate-500">Último pagamento</dt>
                    <dd class="text-base text-slate-900">
                        R$ {{ number_format($empresa->valor_pago, 2, ',', '.') }}
                        @if ($empresa->data_ultimo_pagamento)
                            <span class="block text-xs text-slate-500">
                                {{ $empresa->data_ultimo_pagamento->format('d/m/Y') }}
                            </span>
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        @if ($empresa->observacoes_admin)
            <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50/70 p-4 text-sm text-sky-800">
                <p class="font-semibold uppercase tracking-wide text-sky-500">Observações</p>
                <p class="mt-2 whitespace-pre-line">{{ $empresa->observacoes_admin }}</p>
            </div>
        @endif
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <header class="mb-5 flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-chart-area"></i>
            </span>
            Mensagens enviadas (últimos 30 dias)
        </header>
        <div class="overflow-hidden">
            <canvas id="chartMensagensEmpresa" class="w-full"></canvas>
        </div>
    </article>
</section>

<div id="trialModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl">
        <form action="{{ route('super-admin.empresas.trial', $empresa->id) }}" method="POST"
            class="flex flex-col gap-0">
            @csrf
            <header class="flex items-center justify-between rounded-t-3xl bg-sky-500 px-6 py-4 text-white">
                <div class="flex items-center gap-3 text-lg font-semibold">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20">
                        <i class="fa-solid fa-gift"></i>
                    </span>
                    Liberar acesso trial
                </div>
                <button type="button" id="closeTrialModal"
                    class="rounded-full bg-white/20 p-2 text-white transition hover:bg-white/30">
                    <i class="fa-solid fa-times"></i>
                </button>
            </header>

            <div class="space-y-4 px-6 py-6 text-sm text-slate-600">
                <label class="flex flex-col gap-2">
                    <span class="font-semibold text-slate-600">Quantos dias de trial?</span>
                    <input type="number" name="dias" value="3" min="1" max="90" required
                        class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-slate-700 focus:border-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-200" />
                    <span class="text-xs text-slate-500">Informe o número de dias de acesso gratuito.</span>
                </label>
            </div>

            <footer class="flex justify-end gap-3 rounded-b-3xl bg-slate-50 px-6 py-4">
                <button type="button" id="cancelTrialModal"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                    Cancelar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:bg-sky-400">
                    <i class="fa-solid fa-check text-xs"></i>
                    Liberar trial
                </button>
            </footer>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const chartMensagensCanvas = document.getElementById('chartMensagensEmpresa');
    if (chartMensagensCanvas) {
        const ctx = chartMensagensCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($mensagensPorDia->pluck('dia')->map(fn ($d) => date('d/m', strtotime($d)))->toArray()) !!},
                datasets: [{
                    label: 'Mensagens enviadas',
                    data: {!! json_encode($mensagensPorDia->pluck('total')->toArray()) !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.75)',
                    borderColor: '#4f46e5',
                    borderWidth: 1,
                    borderRadius: 6
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
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    const trialModal = document.getElementById('trialModal');
    const openTrial = document.getElementById('openTrialModal');
    const closeTrial = document.getElementById('closeTrialModal');
    const cancelTrial = document.getElementById('cancelTrialModal');

    const showTrialModal = () => {
        trialModal.classList.remove('hidden');
        trialModal.classList.add('flex');
    };

    const hideTrialModal = () => {
        trialModal.classList.add('hidden');
        trialModal.classList.remove('flex');
    };

    openTrial?.addEventListener('click', showTrialModal);
    closeTrial?.addEventListener('click', hideTrialModal);
    cancelTrial?.addEventListener('click', hideTrialModal);

    trialModal?.addEventListener('click', (event) => {
        if (event.target === trialModal) {
            hideTrialModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && trialModal?.classList.contains('flex')) {
            hideTrialModal();
        }
    });

</script>
@endsection



