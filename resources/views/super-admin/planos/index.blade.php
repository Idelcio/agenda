@extends('super-admin.layouts.app')

@section('title', 'Gerenciar Planos')
@section('page-title', 'Planos de Assinatura')
@section('page-subtitle', 'Gerencie os preços e descontos dos planos')

@section('content')
<section class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3 text-lg font-semibold text-slate-800">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                    <i class="fa-solid fa-tags"></i>
                </span>
                Planos disponíveis
            </div>
            <p class="mt-1 text-sm text-slate-500">
                Os preços e descontos podem ser ajustados abaixo. As alterações são salvas em
                <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">storage/app/plans.json</code>.
            </p>
        </div>
        <span
            class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-200">
            <i class="fa-solid fa-layer-group text-xs"></i>
            {{ count($plans) }} planos
        </span>
    </header>
</section>

<section class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
    @foreach ($plans as $slug => $plan)
        @php
            $finalPrice = $plan['price'];
            if (isset($plan['discount_percent']) && $plan['discount_percent'] > 0) {
                $finalPrice = $plan['price'] * (1 - $plan['discount_percent'] / 100);
            }
            $pricePerMonth = $finalPrice / ($plan['duration_months'] ?? 1);
        @endphp

        <article
            class="group relative flex h-full flex-col overflow-hidden rounded-3xl bg-white p-6 shadow-xl ring-1 ring-indigo-100 transition hover:-translate-y-1 hover:shadow-2xl">
            <div
                class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-80">
            </div>

            <div class="mt-2 text-center">
                <span
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-500 text-white shadow-lg shadow-indigo-600/30">
                    <i class="fa-solid fa-box-open text-xl"></i>
                </span>
                <h3 class="mt-4 text-xl font-semibold text-slate-900">{{ $plan['name'] }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $plan['description'] }}</p>
                <span
                    class="mt-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    {{ $plan['duration_months'] }} {{ $plan['duration_months'] == 1 ? 'mês' : 'meses' }}
                </span>
            </div>

            <div class="mt-6 rounded-2xl bg-slate-50/80 p-4 text-center ring-1 ring-slate-200/60">
                @if (isset($plan['discount_percent']) && $plan['discount_percent'] > 0)
                    <div class="text-sm text-slate-400">
                        <span class="line-through">R$ {{ number_format($plan['price'], 2, ',', '.') }}</span>
                        <span
                            class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">
                            -{{ $plan['discount_percent'] }}%
                        </span>
                    </div>
                    <p class="mt-2 text-3xl font-semibold text-indigo-600">
                        R$ {{ number_format($finalPrice, 2, ',', '.') }}
                    </p>
                @else
                    <p class="text-3xl font-semibold text-indigo-600">
                        R$ {{ number_format($plan['price'], 2, ',', '.') }}
                    </p>
                @endif
                <p class="mt-2 text-xs uppercase tracking-wide text-slate-500">
                    R$ {{ number_format($pricePerMonth, 2, ',', '.') }}/mês
                </p>
            </div>

            <div class="mt-6 flex-1">
                <a href="{{ route('super-admin.planos.editar', $slug) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500">
                    <i class="fa-solid fa-edit text-xs"></i>
                    Editar preços
                </a>
            </div>

            <footer class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-500 ring-1 ring-slate-200/60">
                <i class="fa-solid fa-code mr-2"></i>
                Slug:
                <code>{{ $slug }}</code>
            </footer>
        </article>
    @endforeach
</section>

<section class="mt-6 rounded-3xl border border-sky-200 bg-sky-50/70 p-6 text-sm text-slate-700 shadow-sm">
    <header class="mb-3 flex items-center gap-3 text-base font-semibold text-sky-700">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-500/15 text-sky-600">
            <i class="fa-solid fa-lightbulb"></i>
        </span>
        Como funciona
    </header>
    <ul class="space-y-2">
        <li>• Os planos base são definidos em <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">config/mercadopago.php</code></li>
        <li>• As alterações de preço e desconto são salvas em <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">storage/app/plans.json</code> e têm prioridade sobre o config</li>
        <li>• O sistema utiliza o <strong>PlanService</strong> para mesclar as configurações</li>
        <li>• As alterações são aplicadas imediatamente sem necessidade de reiniciar o servidor</li>
    </ul>
</section>
@endsection


