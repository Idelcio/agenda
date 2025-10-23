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
            <x-input-label for="destinatario_user_id" value="Destinatário (quem vai receber)" />
            <select
                id="destinatario_user_id"
                name="destinatario_user_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Selecionar usuário...</option>
                @if(isset($usuarios))
                    @foreach($usuarios as $usuario)
                        <option
                            value="{{ $usuario->id }}"
                            @selected((int) old('destinatario_user_id', $model->destinatario_user_id ?? '') === $usuario->id)
                        >
                            {{ $usuario->name }} @if($usuario->whatsapp_number) ({{ $usuario->whatsapp_number }}) @endif
                        </option>
                    @endforeach
                @endif
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('destinatario_user_id')" />
            <p class="mt-1 text-xs text-gray-500">Quando este usuário responder pelo WhatsApp, o compromisso será atualizado</p>
        </div>

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

    <div class="border-t-2 border-green-200 pt-4 mt-6 bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg">
        <div class="flex items-start gap-2 mb-3">
            <svg class="w-6 h-6 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
            <div>
                <h4 class="font-bold text-gray-900 text-base">📱 Notificação ao Cliente por WhatsApp</h4>
                <p class="text-sm text-green-700 font-medium mt-1">
                    ✅ Esta seção envia mensagem automaticamente para o cliente
                </p>
                <p class="text-xs text-gray-600 mt-1">
                    Configure para enviar um lembrete automático pelo WhatsApp antes do compromisso.
                </p>
            </div>
        </div>

        <div class="mt-3 flex items-center space-x-2">
            <input type="hidden" name="notificar_whatsapp" value="0">
            <input id="notificar_whatsapp" name="notificar_whatsapp" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('notificar_whatsapp', $model->notificar_whatsapp ?? true)) />
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
                <x-input-label for="whatsapp_numero" value="Número do WhatsApp" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">+55</span>
                    </div>
                    <input
                        id="whatsapp_numero"
                        name="whatsapp_numero"
                        type="text"
                        class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        value="{{ $defaultWhatsappValue }}"
                        placeholder="51999999999"
                        maxlength="13"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500">Digite apenas os números (DDD + telefone)</p>
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

    {{-- Seção de Recorrência --}}
    <div class="border-t-2 border-purple-200 pt-4 mt-6 bg-gradient-to-r from-purple-50 to-indigo-50 p-4 rounded-lg">
        <div class="flex items-start gap-2 mb-3">
            <svg class="w-6 h-6 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <div>
                <h4 class="font-bold text-gray-900 text-base">🔁 Compromisso Recorrente</h4>
                <p class="text-sm text-purple-700 font-medium mt-1">
                    ✅ Ideal para aulas regulares, reuniões semanais ou eventos periódicos
                </p>
                <p class="text-xs text-gray-600 mt-1">
                    Configure para repetir automaticamente este compromisso em intervalos regulares.
                </p>
            </div>
        </div>

        <div class="mt-3 flex items-center space-x-2">
            <input type="hidden" name="recorrente" value="0">
            <input id="recorrente" name="recorrente" type="checkbox" value="1"
                   class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500"
                   @checked(old('recorrente', $model->recorrente ?? false))
                   onchange="document.getElementById('opcoes-recorrencia').classList.toggle('hidden')" />
            <x-input-label for="recorrente" value="Ativar recorrência (repetir compromisso)" />
        </div>

        <div id="opcoes-recorrencia" class="mt-4 space-y-4 {{ old('recorrente', $model->recorrente ?? false) ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="frequencia_recorrencia" value="Frequência de repetição" />
                    <select
                        id="frequencia_recorrencia"
                        name="frequencia_recorrencia"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                    >
                        <option value="">Selecionar...</option>
                        <option value="semanal" @selected(old('frequencia_recorrencia', $model->frequencia_recorrencia ?? '') === 'semanal')>
                            📅 Semanal (toda semana, mesmo dia e hora)
                        </option>
                        <option value="quinzenal" @selected(old('frequencia_recorrencia', $model->frequencia_recorrencia ?? '') === 'quinzenal')>
                            📅 Quinzenal (a cada 2 semanas)
                        </option>
                        <option value="mensal" @selected(old('frequencia_recorrencia', $model->frequencia_recorrencia ?? '') === 'mensal')>
                            📆 Mensal (mesmo dia do mês)
                        </option>
                        <option value="anual" @selected(old('frequencia_recorrencia', $model->frequencia_recorrencia ?? '') === 'anual')>
                            🎂 Anual (uma vez por ano)
                        </option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('frequencia_recorrencia')" />
                </div>

                <div>
                    <x-input-label for="data_fim_recorrencia" value="Repetir até (opcional)" />
                    <x-text-input
                        id="data_fim_recorrencia"
                        name="data_fim_recorrencia"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('data_fim_recorrencia', $model?->data_fim_recorrencia?->format('Y-m-d'))"
                    />
                    <p class="mt-1 text-xs text-gray-500">Deixe em branco para repetir indefinidamente</p>
                    <x-input-error class="mt-2" :messages="$errors->get('data_fim_recorrencia')" />
                </div>
            </div>

            <div class="bg-purple-100 border border-purple-300 rounded-md p-3">
                <p class="text-sm text-purple-900 font-medium">
                    💡 <strong>Como funciona:</strong>
                </p>
                <ul class="text-xs text-purple-800 mt-2 space-y-1 ml-4 list-disc">
                    <li><strong>Semanal:</strong> Ex: Aula toda segunda às 14h</li>
                    <li><strong>Quinzenal:</strong> Ex: Reunião a cada 2 semanas</li>
                    <li><strong>Mensal:</strong> Ex: Pagamento todo dia 5</li>
                    <li><strong>Anual:</strong> Ex: Aniversário, renovação</li>
                </ul>
                <p class="text-xs text-purple-700 mt-2 font-medium">
                    ⚡ Os compromissos serão criados automaticamente pelo sistema!
                </p>
            </div>
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

@if(isset($usuarios))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const destinatarioSelect = document.getElementById('destinatario_user_id');
    const whatsappInput = document.getElementById('whatsapp_numero');
    const tituloInput = document.getElementById('titulo');

    if (whatsappInput) {
        // Remove tudo que não for número e o +55 se existir
        const cleanNumber = (value) => {
            return value.replace(/\D/g, '').replace(/^55/, '');
        };

        // Formata o número enquanto digita
        whatsappInput.addEventListener('input', (e) => {
            let value = e.target.value;
            e.target.value = cleanNumber(value);
        });

        // Remove +55 do valor inicial se existir
        if (whatsappInput.value) {
            whatsappInput.value = cleanNumber(whatsappInput.value);
        }
    }

    if (destinatarioSelect && whatsappInput && tituloInput) {
        // Mapa de usuários com nome e telefone
        const usuarios = {
            @foreach($usuarios as $usuario)
                {{ $usuario->id }}: {
                    nome: '{{ $usuario->name }}',
                    telefone: '{{ $usuario->whatsapp_number ? preg_replace('/^55/', '', preg_replace('/\D/', '', $usuario->whatsapp_number)) : '' }}'
                },
            @endforeach
        };

        destinatarioSelect.addEventListener('change', (e) => {
            const userId = e.target.value;
            if (userId && usuarios[userId]) {
                // Preenche o telefone
                whatsappInput.value = usuarios[userId].telefone;

                // Preenche o título com o nome do usuário se o título estiver vazio
                if (!tituloInput.value || tituloInput.value.trim() === '') {
                    tituloInput.value = usuarios[userId].nome;
                }
            }
        });
    }
});
</script>
@endif
