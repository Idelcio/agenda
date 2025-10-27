@extends('super-admin.layouts.app')

@section('title', 'Editar Plano')
@section('page-title', 'Editar Plano')
@section('page-subtitle', $plan['name'])

@section('content')
@php
    $inputClass =
        'block w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200';
    $finalPrice = $plan['price'];
    if (($plan['discount_percent'] ?? 0) > 0) {
        $finalPrice = $plan['price'] * (1 - $plan['discount_percent'] / 100);
    }
    $pricePerMonth = $finalPrice / max($plan['duration_months'], 1);
@endphp

<section class="mx-auto max-w-4xl space-y-6">
    <article class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200/70">
        <header class="rounded-t-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-white">
            <div class="flex items-center gap-3 text-lg font-semibold">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20">
                    <i class="fa-solid fa-info-circle"></i>
                </span>
                Informações do plano
            </div>
        </header>
        <div class="space-y-6 px-6 py-6 text-sm text-slate-600">
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="space-y-2">
                    <p><span class="font-semibold text-slate-500">Nome:</span> <span
                            class="text-base text-slate-900">{{ $plan['name'] }}</span></p>
                    <p><span class="font-semibold text-slate-500">Descrição:</span> {{ $plan['description'] }}</p>
                    <p><span class="font-semibold text-slate-500">Slug:</span>
                        <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">{{ $plan['slug'] }}</code>
                    </p>
                </div>
                <div class="space-y-2">
                    <p><span class="font-semibold text-slate-500">Duração:</span>
                        {{ $plan['duration_months'] }} {{ $plan['duration_months'] == 1 ? 'mês' : 'meses' }}</p>
                    <p><span class="font-semibold text-slate-500">Preço atual:</span>
                        <span class="font-semibold text-indigo-600">R$ {{ number_format($plan['price'], 2, ',', '.') }}</span>
                    </p>
                    <p><span class="font-semibold text-slate-500">Desconto atual:</span>
                        <span
                            class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">
                            {{ $plan['discount_percent'] ?? 0 }}%
                        </span>
                    </p>
                </div>
            </div>

            <div class="grid gap-4 rounded-2xl bg-slate-50/80 p-4 text-center ring-1 ring-slate-200/60 sm:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Preço base</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900" id="preview-base">
                        R$ {{ number_format($plan['price'], 2, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Preço final</p>
                    <p class="mt-2 text-xl font-semibold text-indigo-600" id="preview-final">
                        R$ {{ number_format($finalPrice, 2, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs font-semibold text-emerald-600" id="preview-discount">
                        -{{ $plan['discount_percent'] ?? 0 }}%
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Por mês</p>
                    <p class="mt-2 text-xl font-semibold text-emerald-600" id="preview-month">
                        R$ {{ number_format($pricePerMonth, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </article>

    <article class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200/70">
        <header class="mb-6 flex items-center gap-3 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                <i class="fa-solid fa-edit"></i>
            </span>
            Editar valores
        </header>

        <form action="{{ route('super-admin.planos.atualizar', $plan['slug']) }}" method="POST" id="editPlanForm"
            class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 sm:grid-cols-2">
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-600">
                        <i class="fa-solid fa-dollar-sign mr-2 text-xs text-indigo-500"></i>Preço (R$) *
                    </span>
                    <input type="number" min="0" step="0.01" name="price" id="price"
                        value="{{ old('price', $plan['price']) }}" required
                        class="{{ $inputClass }} @error('price') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @enderror">
                    @error('price')
                        <p class="text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                    <span class="text-xs text-slate-500">Digite o valor em reais (ex: 59.90).</span>
                </label>

                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-600">
                        <i class="fa-solid fa-percent mr-2 text-xs text-indigo-500"></i>Desconto (%) *
                    </span>
                    <input type="number" min="0" max="100" step="1" name="discount_percent" id="discount_percent"
                        value="{{ old('discount_percent', $plan['discount_percent'] ?? 0) }}" required
                        class="{{ $inputClass }} @error('discount_percent') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @enderror">
                    @error('discount_percent')
                        <p class="text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                    <span class="text-xs text-slate-500">Informe um valor entre 0 e 100.</span>
                </label>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50/70 p-4 text-sm text-slate-700">
                <p class="font-semibold uppercase tracking-wide text-indigo-600">
                    <i class="fa-solid fa-calculator mr-2"></i>Pré-visualização
                </p>
                <p class="mt-1 text-sm text-slate-600">
                    Os valores acima são atualizados automaticamente conforme você altera preço ou desconto.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                <a href="{{ route('super-admin.planos') }}"
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
    </article>

    <article class="rounded-3xl border border-amber-200 bg-amber-50/70 p-6 text-sm text-amber-700 shadow-sm">
        <header class="mb-3 flex items-center gap-3 text-base font-semibold text-amber-700">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/20 text-amber-600">
                <i class="fa-solid fa-exclamation-triangle"></i>
            </span>
            Atenção
        </header>
        <ul class="space-y-2">
            <li>â€¢ As alterações afetam apenas novos pagamentos.</li>
            <li>â€¢ Assinaturas ativas mantêm o valor original.</li>
            <li>â€¢ Os valores são salvos em <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">storage/app/plans.json</code>.</li>
            <li>â€¢ Use sempre valores positivos e coerentes.</li>
        </ul>
    </article>
</section>
@endsection

@section('scripts')
<script>
    const priceInput = document.getElementById('price');
    const discountInput = document.getElementById('discount_percent');
    const durationMonths = {{ $plan['duration_months'] }};

    function formatCurrency(value) {
        return 'R$ ' + value.toFixed(2).replace('.', ',');
    }

    function updatePreview() {
        const price = parseFloat(priceInput.value) || 0;
        const discount = Math.min(Math.max(parseFloat(discountInput.value) || 0, 0), 100);
        const finalPrice = price * (1 - discount / 100);
        const pricePerMonth = finalPrice / Math.max(durationMonths, 1);

        document.getElementById('preview-base').textContent = formatCurrency(price);
        document.getElementById('preview-final').textContent = formatCurrency(finalPrice);
        document.getElementById('preview-month').textContent = formatCurrency(pricePerMonth);
        document.getElementById('preview-discount').textContent = `-${discount.toFixed(0)}%`;
    }

    priceInput.addEventListener('input', updatePreview);
    discountInput.addEventListener('input', updatePreview);
    updatePreview();

    document.getElementById('editPlanForm').addEventListener('submit', (event) => {
        const price = parseFloat(priceInput.value);
        const discount = parseFloat(discountInput.value);

        if (!price || price <= 0) {
            event.preventDefault();
            alert('O preço deve ser maior que zero!');
            priceInput.focus();
            return false;
        }

        if (discount < 0 || discount > 100) {
            event.preventDefault();
            alert('O desconto deve estar entre 0 e 100!');
            discountInput.focus();
            return false;
        }

        return true;
    });
</script>
@endsection


