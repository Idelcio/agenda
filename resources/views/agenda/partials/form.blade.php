@php
    $model = $appointment ?? null;
    $isEdit = filled($model);
    $action = $action ?? ($isEdit ? route('agenda.update', $model) : route('agenda.store'));
    $httpMethod = $httpMethod ?? ($isEdit ? 'PUT' : 'POST');
    $defaultStart = $model?->inicio?->format('Y-m-d\TH:i') ?? now()->addHour()->format('Y-m-d\TH:i');
    $defaultEnd = $model?->fim?->format('Y-m-d\TH:i');
    $defaultWhatsappValue = old('whatsapp_numero', $model->whatsapp_numero ?? $defaultWhatsapp ?? null);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($httpMethod !== 'POST')
        @method($httpMethod)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="titulo" value="Título" />
            <x-text-input
                id="titulo"
                name="titulo"
                type="text"
                class="mt-1 block w-full"
                :value="old('titulo', $model->titulo ?? '')"
                required
            />
            <x-input-error class="mt-2" :messages="$errors->get('titulo')" />
        </div>

        <div>
            <x-input-label for="inicio" value="Início" />
            <x-text-input
                id="inicio"
                name="inicio"
                type="datetime-local"
                class="mt-1 block w-full"
                :value="old('inicio', $defaultStart)"
                required
            />
            <x-input-error class="mt-2" :messages="$errors->get('inicio')" />
        </div>

        <div>
            <x-input-label for="fim" value="Fim (opcional)" />
            <x-text-input
                id="fim"
                name="fim"
                type="datetime-local"
                class="mt-1 block w-full"
                :value="old('fim', $defaultEnd)"
            />
            <x-input-error class="mt-2" :messages="$errors->get('fim')" />
        </div>

        <div class="flex items-center space-x-2 mt-6">
            <input id="dia_inteiro" name="dia_inteiro" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('dia_inteiro', $model->dia_inteiro ?? false)) />
            <x-input-label for="dia_inteiro" value="Dia inteiro" />
        </div>
    </div>

    <div>
        <x-input-label for="descricao" value="Descrição (opcional)" />
        <textarea
            id="descricao"
            name="descricao"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('descricao', $model->descricao ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('descricao')" />
    </div>

    <div class="border-t border-gray-200 pt-4">
        <h4 class="font-medium text-gray-900">Lembrete por WhatsApp</h4>
        <p class="text-sm text-gray-600">Opcional: envie um lembrete automático pelo WhatsApp antes do compromisso.</p>

        <div class="mt-3 flex items-center space-x-2">
            <input id="notificar_whatsapp" name="notificar_whatsapp" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('notificar_whatsapp', $model->notificar_whatsapp ?? false)) />
            <x-input-label for="notificar_whatsapp" value="Ativar lembrete por WhatsApp" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <x-input-label for="antecedencia_minutos" value="Antecedência (minutos)" />
                <select
                    id="antecedencia_minutos"
                    name="antecedencia_minutos"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Selecionar...</option>
                    @foreach ([15, 30, 60, 120, 240, 1440] as $option)
                        <option value="{{ $option }}" @selected((int) old('antecedencia_minutos', $model->antecedencia_minutos ?? '') === $option)>
                            {{ $option === 1440 ? '24 horas' : $option . ' minutos' }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('antecedencia_minutos')" />
            </div>

            <div>
                <x-input-label for="whatsapp_numero" :value="__('WhatsApp number')" />
                <x-text-input
                    id="whatsapp_numero"
                    name="whatsapp_numero"
                    type="text"
                    class="mt-1 block w-full"
                    :value="$defaultWhatsappValue"
                    placeholder="+5511999999999"
                />
                <x-input-error class="mt-2" :messages="$errors->get('whatsapp_numero')" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="whatsapp_mensagem" value="Mensagem personalizada (opcional)" />
            <textarea
                id="whatsapp_mensagem"
                name="whatsapp_mensagem"
                rows="2"
                class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Ex.: Olá! Não esqueça do compromisso importante daqui a pouco."
            >{{ old('whatsapp_mensagem', $model->whatsapp_mensagem ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('whatsapp_mensagem')" />
        </div>
    </div>

    <div>
        <x-primary-button>{{ $submitLabel ?? ($isEdit ? 'Atualizar compromisso' : 'Salvar compromisso') }}</x-primary-button>

        @if ($isEdit)
            <a href="{{ route('agenda.index') }}" class="ml-3 inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
        @endif
    </div>
</form>
