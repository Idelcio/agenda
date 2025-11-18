@php
    $model = $appointment ?? null;
    $isEdit = filled($model);
    $action = $action ?? ($isEdit ? route('agenda.update', $model) : route('agenda.store'));
    $httpMethod = $httpMethod ?? ($isEdit ? 'PUT' : 'POST');
    $defaultStart = $model?->inicio?->format('Y-m-d\TH:i') ?? now()->addHour()->format('Y-m-d\TH:i');
    $defaultEnd = $model?->fim?->format('Y-m-d\TH:i');
    $defaultWhatsappValue = old('whatsapp_numero', $model->whatsapp_numero ?? ($defaultWhatsapp ?? null));
    $quickTemplateCollection = collect($quickMessageTemplates ?? []);
    $quickTemplateLimit = $quickMessageTemplateLimit ?? \App\Models\WhatsAppMessageTemplate::MAX_PER_USER ?? 5;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($httpMethod !== 'POST')
        @method($httpMethod)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="destinatario_user_id" value="Cliente" />
            <select id="destinatario_user_id" name="destinatario_user_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecionar usuário...</option>
                @if (isset($usuarios))
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" data-whatsapp="{{ $usuario->whatsapp_number }}"
                            @selected((int) old('destinatario_user_id', $model->destinatario_user_id ?? '') === $usuario->id)>
                            {{ $usuario->name }} @if ($usuario->whatsapp_number)
                                ({{ $usuario->whatsapp_number }})
                            @endif
                        </option>
                    @endforeach
                @endif
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('destinatario_user_id')" />
            <p class="mt-1 text-xs text-gray-500">Quando este usuário responder pelo WhatsApp, o compromisso será
                atualizado</p>
        </div>

        <div>
            <x-input-label for="titulo" value="Título" />
            <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo', $model->titulo ?? '')"
                required />
            <x-input-error class="mt-2" :messages="$errors->get('titulo')" />
        </div>

        <div>
            <x-input-label for="inicio" value="Início" />
            <x-text-input id="inicio" name="inicio" type="datetime-local" class="mt-1 block w-full"
                :value="old('inicio', $defaultStart)" required />
            <x-input-error class="mt-2" :messages="$errors->get('inicio')" />
        </div>

        <div>
            <x-input-label for="fim" value="Fim (opcional)" />
            <x-text-input id="fim" name="fim" type="datetime-local" class="mt-1 block w-full"
                :value="old('fim', $defaultEnd)" />
            <x-input-error class="mt-2" :messages="$errors->get('fim')" />
        </div>

        <div class="flex items-center space-x-2 mt-6">
            <input id="dia_inteiro" name="dia_inteiro" type="checkbox" value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('dia_inteiro', $model->dia_inteiro ?? false)) />
            <x-input-label for="dia_inteiro" value="Dia inteiro" />
        </div>
    </div>

    <div>
        <x-input-label for="descricao" value="Descrição (opcional)" />
        <textarea id="descricao" name="descricao" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('descricao', $model->descricao ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('descricao')" />
    </div>

    <div class="border-t-2 border-green-200 pt-4 mt-6 bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg">
        <div class="flex items-start gap-2 mb-3">
            <svg class="w-6 h-6 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
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
            <input id="notificar_whatsapp" name="notificar_whatsapp" type="checkbox" value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('notificar_whatsapp', $model->notificar_whatsapp ?? true)) />
            <x-input-label for="notificar_whatsapp" value="Ativar lembrete por WhatsApp" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <x-input-label for="antecedencia_minutos" value="Antecedência (minutos)" />
                <select id="antecedencia_minutos" name="antecedencia_minutos"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <input id="whatsapp_numero" name="whatsapp_numero" type="text"
                        class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        value="{{ $defaultWhatsappValue }}" placeholder="51999999999" maxlength="13" />
                </div>
                <p class="mt-1 text-xs text-gray-500">Digite apenas os números (DDD + telefone)</p>
                <x-input-error class="mt-2" :messages="$errors->get('whatsapp_numero')" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="whatsapp_mensagem" value="Mensagem personalizada (opcional)" />
            <textarea id="whatsapp_mensagem" name="whatsapp_mensagem" rows="2"
                class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Ex.: Olá! Não esqueça do compromisso importante daqui a pouco.">{{ old('whatsapp_mensagem', $model->whatsapp_mensagem ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('whatsapp_mensagem')" />

            <div class="mt-3 space-y-3 rounded-md border border-indigo-100 bg-indigo-50/60 p-4"
                data-quick-template-controls data-target="whatsapp_mensagem" data-limit="{{ $quickTemplateLimit }}"
                data-save-url="{{ route('agenda.quick-messages.store') }}"
                data-update-url-template="{{ route('agenda.quick-messages.update', ['template' => '__TEMPLATE__']) }}"
                data-delete-url-template="{{ route('agenda.quick-messages.destroy', ['template' => '__TEMPLATE__']) }}"
                data-templates='@json($quickTemplateCollection->map(fn($t) => ["id" => $t->id, "message" => $t->message]))'>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                            Mensagens prontas
                        </span>
                        <span class="text-xs text-gray-500" data-template-count>
                            {{ $quickTemplateCollection->count() }}/{{ $quickTemplateLimit }} salvas
                        </span>
                    </div>
                    <button type="button"
                        class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:bg-indigo-400"
                        data-action="save-template">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        <span data-template-save-label>Guardar mensagem</span>
                    </button>
                </div>

                <p class="text-xs text-gray-500" data-templates-empty
                    @if ($quickTemplateCollection->isNotEmpty()) style="display: none;" @endif>
                    Nenhuma mensagem pronta ainda. Clique em "Guardar mensagem" para salvar textos recorrentes.
                </p>

                <div class="flex gap-3 overflow-x-auto pb-2" data-template-list>
                    {{-- Cards renderizados dinamicamente pelo JavaScript --}}
                </div>

                <p class="hidden text-xs font-medium text-emerald-600" data-template-feedback></p>
            </div>

            <div class="mt-4 p-3 bg-blue-50 rounded-md border border-blue-200">
                <x-input-label for="tipo_mensagem" value="Tipo de Mensagem" class="mb-2" />
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input id="tipo_compromisso" name="tipo_mensagem" type="radio" value="compromisso"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                            @checked(old('tipo_mensagem', $model->tipo_mensagem ?? 'compromisso') === 'compromisso') />
                        <label for="tipo_compromisso" class="ml-2 block text-sm text-gray-900">
                            📅 <strong>Compromisso</strong> - Cliente pode confirmar ou cancelar (envia botões 1 e 2)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="tipo_aviso" name="tipo_mensagem" type="radio" value="aviso"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                            @checked(old('tipo_mensagem', $model->tipo_mensagem ?? 'compromisso') === 'aviso') />
                        <label for="tipo_aviso" class="ml-2 block text-sm text-gray-900">
                            🔔 <strong>Aviso/Lembrete</strong> - Apenas informativo, sem necessidade de resposta
                        </label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-blue-700">
                    💡 Use "Aviso" para lembretes simples como "Não esqueça de estudar inglês hoje!" ou avisos gerais que não precisam de confirmação.
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('tipo_mensagem')" />
            </div>
        </div>
    </div>

    {{-- Seção de Recorrência --}}
    <div class="border-t-2 border-purple-200 pt-4 mt-6 bg-gradient-to-r from-purple-50 to-indigo-50 p-4 rounded-lg">
        <div class="flex items-start gap-2 mb-3">
            <svg class="w-6 h-6 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
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

        <div id="opcoes-recorrencia"
            class="mt-4 space-y-4 {{ old('recorrente', $model->recorrente ?? false) ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="frequencia_recorrencia" value="Frequência de repetição" />
                    <select id="frequencia_recorrencia" name="frequencia_recorrencia"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
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
                    <x-text-input id="data_fim_recorrencia" name="data_fim_recorrencia" type="date"
                        class="mt-1 block w-full" :value="old('data_fim_recorrencia', $model?->data_fim_recorrencia?->format('Y-m-d'))" />
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

    <div class="flex justify-center">
        <button type="submit" class="inline-flex items-center justify-center px-8 py-4 bg-green-600 border border-transparent rounded-xl font-bold text-base text-white uppercase tracking-wider hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg hover:shadow-xl">
            {{ $submitLabel ?? ($isEdit ? 'Atualizar compromisso' : 'Salvar compromisso') }}
        </button>

        @if ($isEdit)
            <a href="{{ route('agenda.index') }}"
                class="ml-3 inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
        @endif
    </div>
</form>

@if (isset($usuarios))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Remove tudo que não for número e o +55 se existir
            const cleanNumber = (value) => {
                return value.replace(/\D/g, '').replace(/^55/, '');
            };

            // 🔹 Busca TODOS os formulários (mobile + desktop) e configura cada um
            document.querySelectorAll('form').forEach((form) => {
                const destinatarioSelect = form.querySelector('#destinatario_user_id');
                const whatsappInput = form.querySelector('#whatsapp_numero');
                const tituloInput = form.querySelector('#titulo');

                // Configura a limpeza do número WhatsApp
                if (whatsappInput) {
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

                // Configura o auto-fill quando seleciona cliente
                if (destinatarioSelect && whatsappInput && tituloInput) {
                    console.log('✅ Auto-fill de WhatsApp habilitado para formulário');

                    destinatarioSelect.addEventListener('change', (e) => {
                        const selectedOption = e.target.options[e.target.selectedIndex];
                        console.log('👤 Cliente selecionado:', selectedOption.text);

                        // Pega o número do atributo data-whatsapp da option selecionada
                        const whatsappNumber = selectedOption.getAttribute('data-whatsapp');
                        console.log('📞 Número do data-whatsapp:', whatsappNumber);

                        if (whatsappNumber) {
                            // Remove o +55 se existir e limpa o número
                            const cleanedNumber = whatsappNumber.replace(/\D/g, '').replace(/^55/, '');
                            console.log('🧹 Número limpo:', cleanedNumber);

                            whatsappInput.value = cleanedNumber;
                            console.log('✅ Campo atualizado com:', whatsappInput.value);

                            // Preenche o título com o nome do usuário se o título estiver vazio
                            const userName = selectedOption.text.split('(')[0].trim();
                            if (!tituloInput.value || tituloInput.value.trim() === '') {
                                tituloInput.value = userName;
                                console.log('📝 Título preenchido:', userName);
                            }
                        } else {
                            console.warn('⚠️ Nenhum número de WhatsApp encontrado no data-whatsapp');
                        }
                    });
                }
            });
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        const quickTemplateContainers = Array.from(document.querySelectorAll('[data-quick-template-controls]'));

        if (!quickTemplateContainers.length) {
            return;
        }

        const useIcon = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 012 2v12a2 2 0 01-2 2h-2m-8 0H6a2 2 0 01-2-2V6a2 2 0 012-2h2m2-1v18m4-18v18"></path></svg>';
        const editIcon = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>';
        const deleteIcon = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

        const normalizeTemplates = (templates) => Array.isArray(templates)
            ? templates.map((template) => ({
                id: template.id,
                message: template.message ?? '',
            }))
            : [];

        const findTemplate = (container, templateId) => {
            return (container.__templates || []).find((template) => String(template.id) === String(templateId));
        };

        const showFeedback = (container, message, type = 'success') => {
            const feedback = container.querySelector('[data-template-feedback]');
            if (!feedback) {
                return;
            }

            feedback.textContent = message;
            feedback.classList.remove('hidden', 'text-emerald-600', 'text-red-600');
            feedback.classList.add(type === 'error' ? 'text-red-600' : 'text-emerald-600');

            if (feedback.__timeoutId) {
                window.clearTimeout(feedback.__timeoutId);
            }

            feedback.__timeoutId = window.setTimeout(() => {
                feedback.classList.add('hidden');
                feedback.__timeoutId = null;
            }, 4000);
        };

        const resolveTextarea = (container, targetId) => {
            if (!targetId) {
                return null;
            }

            const form = container.closest('form');
            const selector = `#${targetId}`;

            return (form ? form.querySelector(selector) : null) ?? document.getElementById(targetId);
        };

        const applyMessage = (container, targetId, message, successMessage = null) => {
            const textarea = resolveTextarea(container, targetId);

            if (!textarea) {
                showFeedback(container, 'Campo de mensagem nao encontrado.', 'error');
                return false;
            }

            if (!message) {
                showFeedback(container, 'Mensagem salva nao encontrada.', 'error');
                return false;
            }

            textarea.value = message;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            textarea.dispatchEvent(new Event('change', { bubbles: true }));
            textarea.focus();

            if (successMessage) {
                showFeedback(container, successMessage, 'success');
            }

            return true;
        };

        const updateControls = (container) => {
            const templates = container.__templates || [];
            const limit = Number(container.dataset.limit || 5);
            const countLabel = container.querySelector('[data-template-count]');
            const emptyMessage = container.querySelector('[data-templates-empty]');
            const saveButton = container.querySelector('[data-action="save-template"]');
            const isEditing = Boolean(container.dataset.editingTemplateId);

            if (countLabel) {
                countLabel.textContent = `${templates.length}/${limit} salvas`;
            }

            if (emptyMessage) {
                emptyMessage.style.display = templates.length === 0 ? '' : 'none';
            }

            if (saveButton) {
                const limitReached = !isEditing && templates.length >= limit;
                saveButton.disabled = limitReached;
                saveButton.classList.toggle('opacity-60', limitReached);
                const saveLabel = saveButton.querySelector('[data-template-save-label]');
                if (saveLabel) {
                    saveLabel.textContent = isEditing ? 'Atualizar mensagem' : 'Guardar mensagem';
                }

                if (limitReached) {
                    saveButton.setAttribute('title', 'Limite atingido. Exclua uma mensagem salva para adicionar outra.');
                } else {
                    saveButton.removeAttribute('title');
                }
            }
        };

        const createActionButton = (container, template, options) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = options.className;
            button.dataset.action = options.action;
            button.dataset.templateId = String(template.id);
            button.dataset.message = template.message;

            const targetId = container.dataset.target;
            if (targetId) {
                button.dataset.target = targetId;
            }

            button.insertAdjacentHTML('beforeend', options.icon);
            button.appendChild(document.createTextNode(` ${options.label}`));

            return button;
        };

        const renderTemplates = (container) => {
            const list = container.querySelector('[data-template-list]');
            if (!list) {
                return;
            }

            const targetId = container.dataset.target;
            const templates = container.__templates || [];

            list.innerHTML = '';

            templates.forEach((template) => {
                // Card clicável horizontal
                const card = document.createElement('div');
                card.className = 'flex-shrink-0 w-64 rounded-lg border border-indigo-200 bg-white shadow-sm p-4 cursor-pointer hover:border-indigo-400 hover:shadow-md transition-all';
                card.dataset.templateId = String(template.id);
                card.dataset.templateMessage = template.message;
                card.dataset.action = 'apply-template';
                card.dataset.message = template.message;
                if (targetId) {
                    card.dataset.target = targetId;
                }

                const contentContainer = document.createElement('div');
                contentContainer.className = 'space-y-3';

                const messageParagraph = document.createElement('p');
                messageParagraph.className = 'text-sm text-slate-800 whitespace-pre-line leading-relaxed line-clamp-3';
                messageParagraph.dataset.templateMessageText = '1';
                messageParagraph.textContent = template.message;

                const actions = document.createElement('div');
                actions.className = 'flex flex-wrap gap-2';

                const editBtn = createActionButton(container, template, {
                    action: 'edit-template',
                    className: 'inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1',
                    icon: editIcon,
                    label: 'Editar',
                });
                editBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const message = template.message;
                    const targetId = container.dataset.target;
                    if (applyMessage(container, targetId, message)) {
                        container.dataset.selectedTemplateId = String(template.id);
                        setEditingState(container, template.id);
                        showFeedback(container, 'Mensagem carregada para edicao. Ajuste e clique em Guardar para atualizar.', 'success');
                    }
                });

                const deleteBtn = createActionButton(container, template, {
                    action: 'delete-template',
                    className: 'inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-medium text-rose-600 transition hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1',
                    icon: deleteIcon,
                    label: 'Excluir',
                });
                deleteBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    handleDelete(container, deleteBtn, template.id);
                });

                actions.appendChild(editBtn);
                actions.appendChild(deleteBtn);

                contentContainer.appendChild(messageParagraph);
                contentContainer.appendChild(actions);
                card.appendChild(contentContainer);
                list.appendChild(card);
            });

            updateControls(container);
        };

        const setTemplates = (container, templates) => {
            container.__templates = normalizeTemplates(templates);
            renderTemplates(container);
        };

        const readTemplatesFromDom = (container) => {
            const list = container.querySelector('[data-template-list]');
            if (!list) {
                return [];
            }

            return Array.from(list.querySelectorAll('[data-template-id]')).map((card) => ({
                id: card.dataset.templateId,
                message: card.dataset.templateMessage ?? card.querySelector('[data-template-message-text]')?.textContent ?? '',
            }));
        };

        const setEditingState = (container, templateId = null) => {
            if (templateId) {
                container.dataset.editingTemplateId = String(templateId);
            } else {
                delete container.dataset.editingTemplateId;
            }

            updateControls(container);
        };

        const handleSave = async (container, trigger) => {
            const targetId = trigger.dataset.target || container.dataset.target;
            const textarea = resolveTextarea(container, targetId);

            if (!textarea) {
                showFeedback(container, 'Campo de mensagem nao encontrado.', 'error');
                return;
            }

            const message = textarea.value.trim();

            if (!message) {
                showFeedback(container, 'Escreva uma mensagem antes de salvar.', 'error');
                return;
            }

            if (message.length > 500) {
                showFeedback(container, 'A mensagem pode ter no maximo 500 caracteres.', 'error');
                return;
            }

            const saveUrl = container.dataset.saveUrl;
            const updateUrlTemplate = container.dataset.updateUrlTemplate;
            const editingTemplateId = container.dataset.editingTemplateId;
            const isEditing = Boolean(editingTemplateId);
            const requestUrl = isEditing
                ? updateUrlTemplate?.replace('__TEMPLATE__', editingTemplateId)
                : saveUrl;
            const method = isEditing ? 'PATCH' : 'POST';

            if (!requestUrl) {
                showFeedback(container, 'Nao foi possivel encontrar a rota para salvar.', 'error');
                return;
            }

            trigger.disabled = true;

            try {
                const response = await fetch(requestUrl, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ mensagem: message }),
                });

                const payload = await response.json().catch(() => null);

                if (response.ok) {
                    setTemplates(container, payload?.templates ?? container.__templates);
                    setEditingState(container, null);
                    delete container.dataset.selectedTemplateId;
                    showFeedback(container, payload?.message ?? (isEditing ? 'Mensagem atualizada.' : 'Mensagem salva.'), 'success');
                } else {
                    const errorMessage = payload?.message ?? 'Nao foi possivel salvar a mensagem.';
                    showFeedback(container, errorMessage, 'error');
                }
            } catch (_error) {
                showFeedback(container, 'Erro ao salvar mensagem. Tente novamente.', 'error');
            } finally {
                trigger.disabled = false;
            }
        };

        const handleDelete = async (container, trigger, templateId) => {
            const deleteUrlTemplate = container.dataset.deleteUrlTemplate;

            if (!deleteUrlTemplate) {
                showFeedback(container, 'Nao foi possivel encontrar a rota para exclusao.', 'error');
                return;
            }

            if (!window.confirm('Deseja excluir esta mensagem salva?')) {
                return;
            }

            const requestUrl = deleteUrlTemplate.replace('__TEMPLATE__', templateId);

            trigger.disabled = true;

            try {
                const response = await fetch(requestUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                    },
                });

                const payload = await response.json().catch(() => null);

                if (response.ok) {
                    setTemplates(container, payload?.templates ?? container.__templates);
                    if (container.dataset.selectedTemplateId === String(templateId)) {
                        delete container.dataset.selectedTemplateId;
                    }
                    if (container.dataset.editingTemplateId === String(templateId)) {
                        setEditingState(container, null);
                    }
                    showFeedback(container, payload?.message ?? 'Mensagem excluida.', 'success');
                } else {
                    const errorMessage = payload?.message ?? 'Nao foi possivel excluir a mensagem.';
                    showFeedback(container, errorMessage, 'error');
                }
            } catch (_error) {
                showFeedback(container, 'Erro ao excluir mensagem. Tente novamente.', 'error');
            } finally {
                trigger.disabled = false;
            }
        };

        quickTemplateContainers.forEach((container) => {
            if (container.dataset.qtBound === '1') {
                return;
            }
            container.dataset.qtBound = '1';

            // Lê templates do atributo data-templates ou do DOM
            const initialTemplates = container.dataset.templates
                ? JSON.parse(container.dataset.templates)
                : readTemplatesFromDom(container);

            setTemplates(container, initialTemplates);

            container.addEventListener('change', (event) => {
                const checkbox = event.target.closest('input[data-action="select-template"]');
                if (!checkbox || !container.contains(checkbox)) {
                    return;
                }

                if (!checkbox.checked) {
                    delete container.dataset.selectedTemplateId;
                    return;
                }

                container.querySelectorAll('input[data-action="select-template"]').forEach((input) => {
                    if (input !== checkbox) {
                        input.checked = false;
                    }
                });

                const message = checkbox.dataset.message ?? findTemplate(container, checkbox.dataset.templateId)?.message ?? '';
                const targetId = checkbox.dataset.target || container.dataset.target;

                if (!applyMessage(container, targetId, message, 'Mensagem aplicada.')) {
                    checkbox.checked = false;
                    return;
                }

                container.dataset.selectedTemplateId = checkbox.dataset.templateId;
                setEditingState(container, null);
            });

            container.addEventListener('click', (event) => {
                let trigger = event.target;
                while (trigger && trigger !== container && typeof trigger.matches === 'function' && !trigger.matches('button[data-action], div[data-action="apply-template"]')) {
                    trigger = trigger.parentElement || trigger.parentNode || null;
                }

                if (!trigger || trigger === container || typeof trigger.matches !== 'function' || !trigger.matches('button[data-action], div[data-action="apply-template"]')) {
                    return;
                }

                const action = trigger.dataset.action;
                const templateId = trigger.dataset.templateId;
                const targetId = trigger.dataset.target || container.dataset.target;

                if (action === 'apply-template') {
                    const message = trigger.dataset.message ?? findTemplate(container, templateId)?.message ?? '';

                    if (applyMessage(container, targetId, message, 'Mensagem aplicada.')) {
                        container.dataset.selectedTemplateId = templateId ?? '';
                        setEditingState(container, null);
                    }

                    return;
                }

                if (action === 'edit-template') {
                    const message = trigger.dataset.message ?? findTemplate(container, templateId)?.message ?? '';

                    if (applyMessage(container, targetId, message)) {
                        container.dataset.selectedTemplateId = templateId ?? '';
                        setEditingState(container, templateId);
                        showFeedback(container, 'Mensagem carregada para edicao. Ajuste e clique em Guardar para atualizar.', 'success');
                    }

                    return;
                }

                if (action === 'save-template') {
                    handleSave(container, trigger);
                    return;
                }

                if (action === 'delete-template' && templateId) {
                    handleDelete(container, trigger, templateId);
                }
            });
        });
    });
</script>
