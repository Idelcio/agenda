@php
    use Illuminate\Support\Str;

    $statusLabels = [
        'pendente' => 'Pendente',
        'confirmado' => 'Confirmado',
        'cancelado' => 'Cancelado',
        'concluido' => 'Concluido',
    ];

    $statusStyles = [
        'pendente' => 'bg-amber-100 text-amber-700',
        'confirmado' => 'bg-emerald-100 text-emerald-700',
        'cancelado' => 'bg-rose-100 text-rose-700',
        'concluido' => 'bg-slate-200 text-slate-700',
    ];

    $recentReminderId = session('reminder_sent');
    $recentReminderId = is_numeric($recentReminderId) ? (int) $recentReminderId : null;

    $quickMessageTemplates = $quickMessageTemplates ?? collect();
    $quickMessageTemplateLimit = $quickMessageTemplateLimit ?? 5;
    $quickTemplateOptions = $quickMessageTemplates->map(function ($template) {
        return [
            'id' => $template->id,
            'message' => $template->message,
            'preview' => Str::limit($template->message, 80),
        ];
    })->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agenda
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
                    <strong>Ops!</strong>
                    <p class="mt-2 text-sm">Verifique os campos destacados e tente novamente.</p>
                </div>
            @endif

            @if (session('status'))
                @php
                    $statusKey = 'messages.' . session('status');
                    $statusMessage = trans($statusKey);
                @endphp
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded">
                    {{ $statusMessage !== $statusKey ? $statusMessage : 'Operacao realizada com sucesso.' }}
                </div>
            @endif

            {{-- Estatsticas Principais --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 p-5 shadow-md sm:rounded-lg border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-blue-600">Total de Compromissos</p>
                            <p class="text-2xl font-bold text-blue-900 mt-2">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-blue-500 p-2 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-amber-50 to-amber-100 p-5 shadow-md sm:rounded-lg border-l-4 border-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-amber-600">Pendentes</p>
                            <p class="text-2xl font-bold text-amber-900 mt-2">{{ $stats['pendentes'] }}</p>
                        </div>
                        <div class="bg-amber-500 p-2 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-5 shadow-md sm:rounded-lg border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-emerald-600">Confirmados</p>
                            <p class="text-2xl font-bold text-emerald-900 mt-2">{{ $stats['confirmados'] }}</p>
                        </div>
                        <div class="bg-emerald-500 p-2 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-purple-50 to-purple-100 p-5 shadow-md sm:rounded-lg border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-purple-600">Concluidos</p>
                            <p class="text-2xl font-bold text-purple-900 mt-2">{{ $stats['concluidos'] }}</p>
                        </div>
                        <div class="bg-purple-500 p-2 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Calendrio --}}
            <div class="bg-white shadow-md sm:rounded-lg border-t-4 border-purple-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">Calendrio de Agendamentos</h3>
                        </div>
                        <a href="{{ route('agenda.pdf-semanal') }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Gerar PDF Semanal
                        </a>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Visualize seus compromissos por dia, semana ou mês. Clique em um horário para criar novo
                        agendamento.
                    </p>
                    <div id="fullcalendar"></div>
                </div>
            </div>

            {{-- Estatsticas de Lembretes WhatsApp --}}
            <div class="bg-white shadow-md sm:rounded-lg border-t-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Status de Lembretes WhatsApp</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <p class="text-xs uppercase font-semibold text-indigo-600">Com Lembrete Ativo</p>
                            <p class="text-2xl font-bold text-indigo-900 mt-1">{{ $stats['com_lembrete'] }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <p class="text-xs uppercase font-semibold text-green-600">Lembretes Enviados</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['lembretes_enviados'] }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <p class="text-xs uppercase font-semibold text-yellow-600">Aguardando Horrio</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $stats['lembretes_pendentes'] }}</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <p class="text-xs uppercase font-semibold text-red-600">Falhas no Envio</p>
                            <p class="text-2xl font-bold text-red-900 mt-1">{{ $stats['lembretes_falharam'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg border-t-4 border-indigo-500">
                <div class="p-6">
                    <!-- Wrapper flex responsivo -->
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-4">
                        <!-- cone e texto principal -->
                        <div class="flex items-start gap-3 flex-1">
                            <div class="bg-indigo-100 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Novo Compromisso Interno</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="font-semibold text-indigo-600"> Para uso da empresa:</span>
                                    Registre o compromisso no seu sistema interno.
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                     <span class="font-medium">Importante:</span> Este formulrio apenas
                                    <strong>salva na sua agenda</strong>. Para <strong>notificar o cliente</strong>,
                                    configure o lembrete por WhatsApp abaixo.
                                </p>
                            </div>
                        </div>

                        <!-- Boto "Novo Cliente" que vai para baixo no mobile -->
                        <div class="w-full lg:w-auto mt-4 lg:mt-0">
                            <a href="{{ route('clientes.create') }}"
                                class="inline-flex items-center justify-center w-full lg:w-auto px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Novo Cliente
                            </a>
                        </div>
                    </div>

                    {{-- Formulrio includo --}}
                    @include('agenda.partials.form', [
                        'appointment' => null,
                        'defaultWhatsapp' => $defaultWhatsapp,
                        'usuarios' => $usuarios,
                        'submitLabel' => 'Salvar no Sistema',
                        'action' => route('agenda.store'),
                        'httpMethod' => 'POST',
                    ])
                </div>
            </div>


            {{-- <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">WhatsApp rapido</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Envie uma mensagem individual para qualquer numero habilitado no WhatsApp; apos o texto,
                        enviamos botoes de confirmacao/cancelamento para testar o fluxo.
                    </p>

                    @error('quick_whatsapp')
                        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <form method="POST" action="{{ route('agenda.quick-whatsapp') }}" class="space-y-4"
                        enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-input-label for="destinatario" value="Numero de destino" />
                            <x-text-input id="destinatario" name="destinatario" type="text" class="mt-1 block w-full"
                                value="{{ old('destinatario', $quickMessageDefaults['destinatario']) }}"
                                placeholder="+5511999999999" />
                            <x-input-error class="mt-2" :messages="$errors->get('destinatario')" />
                        </div>

                        <div>
                            <x-input-label for="mensagem" value="Mensagem" />
                            <textarea id="mensagem" name="mensagem" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Digite a mensagem que deseja enviar">{{ old('mensagem', 'Ola! Esta e uma mensagem de teste do Agendoo.') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('mensagem')" />
                        </div>

                        <div>
                            <x-input-label for="attachment" value="Anexo (opcional)" />
                            <input id="attachment" name="attachment" type="file"
                                class="mt-1 block w-full text-sm text-gray-700" />
                            <p class="mt-1 text-xs text-gray-500">Aceita imagens (JPEG, PNG, WEBP, GIF) ou PDF de ate 5
                                MB.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <x-primary-button>Enviar mensagem</x-primary-button>
                            @if (session('quick_message_sent'))
                                @php
                                    $quickPhone = session('quick_message_sent');
                                    $quickPhone = $quickPhone ? '+' . ltrim($quickPhone, '+') : null;
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                                    Enviado
                                </span>
                                @if ($quickPhone)
                                    <span class="text-xs text-emerald-700">Destino: {{ $quickPhone }}</span>
                                @endif
                            @endif
                            <span class="text-xs text-gray-500">Necessario configurar a API Brasil e autorizar o
                                numero.</span>
                        </div>
                    </form>
                </div>
            </div> --}}

            @if ($dueReminders->isNotEmpty())
                <div
                    class="bg-gradient-to-r from-red-50 to-orange-50 shadow-lg sm:rounded-lg border-l-4 border-red-500">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-lg font-bold text-red-900">
                                Lembretes Prontos para Envio
                            </h3>
                            <span class="ml-2 bg-red-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                                {{ $dueReminders->count() }}
                            </span>
                        </div>

                        <p class="text-sm text-red-700 mb-4">
                            Estes compromissos atingiram o horrio programado. Configure e envie manualmente ou aguarde
                            o envio automtico.
                        </p>

                        <div class="space-y-4">
                            @foreach ($dueReminders as $appointment)
                                <div class="rounded-lg border-2 border-red-300 bg-white p-4 shadow-sm">
                                    <div class="flex items-start gap-2 mb-3">
                                        <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-base font-bold text-gray-900">
                                                {{ $appointment->titulo }}
                                            </p>
                                            @if ($appointment->contact_name)
                                                <p class="text-sm text-gray-600">
                                                    <strong>Cliente:</strong> {{ $appointment->contact_name }}
                                                </p>
                                            @endif
                                            @if ($appointment->contact_phone)
                                                <p class="text-xs text-gray-500">
                                                    <strong>WhatsApp:</strong> {{ $appointment->contact_phone }}
                                                </p>
                                            @endif
                                            <p class="text-sm text-gray-700 mt-1">
                                                <strong>Compromisso:</strong>
                                                {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y \\s H:i') }}
                                            </p>
                                            <p class="text-xs text-gray-600 mt-0.5">
                                                <strong>Lembrete programado:</strong>
                                                {{ $appointment->lembrar_em?->timezone(config('app.timezone'))->format('d/m/Y \\s H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    @php
                                        $reminderJustSent =
                                            $recentReminderId !== null && $recentReminderId === $appointment->id;
                                    @endphp

                                    @can('remind', $appointment)
                                        @if ($reminderJustSent)
                                            <div
                                                class="flex items-center gap-2 bg-green-50 p-3 rounded border border-green-200">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span class="text-sm font-semibold text-green-700">
                                                    Lembrete enviado com sucesso!
                                                </span>
                                            </div>
                                        @else
                                            {{-- Formulrio do lembrete --}}
                                            <form method="POST" action="{{ route('agenda.quick-whatsapp') }}"
                                                class="space-y-3 mt-3 pt-3 border-t border-red-200"
                                                enctype="multipart/form-data"
                                                data-lembrete-hora="{{ $appointment->lembrar_em?->timezone(config('app.timezone'))->format('Y-m-d\TH:i:s') }}">
                                                @csrf

                                                <input type="hidden" name="appointment_id"
                                                    value="{{ $appointment->id }}">

                                                <div>
                                                    <x-input-label for="destinatario_{{ $appointment->id }}"
                                                        value="Nmero de destino" class="text-xs" />
                                                    <x-text-input id="destinatario_{{ $appointment->id }}"
                                                        name="destinatario" type="text"
                                                        class="mt-1 block w-full text-sm"
                                                        value="{{ old('destinatario', $appointment->contact_phone ?? '+' . ($appointment->whatsapp_numero ?? $appointment->user->whatsapp_number)) }}"
                                                        placeholder="+5511999999999" required />
                                                </div>

                                                <div>
                                                    <x-input-label for="mensagem_{{ $appointment->id }}" value="Mensagem"
                                                        class="text-xs" />
                                                    <textarea id="mensagem_{{ $appointment->id }}" name="mensagem" rows="3"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                        placeholder="Digite a mensagem do lembrete" required>{{ old('mensagem', $appointment->whatsapp_mensagem ?? sprintf('Ol! Voc tem um agendamento de %s em %s.', $appointment->titulo, $appointment->inicio?->timezone(config('app.timezone'))->format('d/m/Y \\s H:i'))) }}</textarea>
                                                </div>

                                                <div class="mt-3 space-y-3 rounded-md border border-indigo-100 bg-indigo-50/60 p-3"
                                                    data-quick-template-controls data-target="mensagem_{{ $appointment->id }}"
                                                    data-limit="{{ $quickMessageTemplateLimit }}"
                                                    data-save-url="{{ route('agenda.quick-messages.store') }}"
                                                    data-update-url-template="{{ route('agenda.quick-messages.update', ['template' => '__TEMPLATE__']) }}"
                                                    data-delete-url-template="{{ route('agenda.quick-messages.destroy', ['template' => '__TEMPLATE__']) }}">
                                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                                                                Mensagens prontas
                                                            </span>
                                                            <span class="text-xs text-gray-500" data-template-count>
                                                                {{ $quickMessageTemplates->count() }}/{{ $quickMessageTemplateLimit }} salvas
                                                            </span>
                                                        </div>
                                                        <button type="button"
                                                            class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:bg-indigo-400"
                                                            data-action="save-template">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            <span data-template-save-label>Guardar mensagem</span>
                                                        </button>
                                                    </div>

                                                    <p class="text-xs text-gray-500" data-templates-empty
                                                        @if ($quickMessageTemplates->isNotEmpty()) style="display: none;" @endif>
                                                        Nenhuma mensagem pronta ainda. Clique em "Guardar mensagem" para salvar textos recorrentes.
                                                    </p>

                                                    <div class="flex gap-3 overflow-x-auto pb-2" data-template-list>
                                                        @foreach ($quickMessageTemplates as $template)
                                                            <div class="flex-shrink-0 w-64 rounded-lg border border-indigo-200 bg-white shadow-sm p-4 cursor-pointer hover:border-indigo-400 hover:shadow-md transition-all"
                                                                data-template-id="{{ $template->id }}"
                                                                data-template-message="{{ e($template->message) }}"
                                                                data-action="apply-template"
                                                                data-message="{{ e($template->message) }}"
                                                                data-target="mensagem_{{ $appointment->id }}">
                                                                <div class="space-y-3">
                                                                    <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed line-clamp-3" data-template-message-text>{{ $template->message }}</p>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button type="button"
                                                                            class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1"
                                                                            data-action="edit-template"
                                                                            data-template-id="{{ $template->id }}"
                                                                            data-message="{{ e($template->message) }}"
                                                                            data-target="mensagem_{{ $appointment->id }}"
                                                                            onclick="event.stopPropagation()">
                                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                                                viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round" stroke-width="2"
                                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                            </svg>
                                                                            Editar
                                                                        </button>
                                                                        <button type="button"
                                                                            class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-medium text-rose-600 transition hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1"
                                                                            data-action="delete-template"
                                                                            data-template-id="{{ $template->id }}"
                                                                            onclick="event.stopPropagation()">
                                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round" stroke-width="2"
                                                                                        d="M6 18L18 6M6 6l12 12" />
                                                                                </svg>
                                                                                Excluir
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <p class="hidden text-xs font-medium text-emerald-600" data-template-feedback></p>
                                                </div>

                                                <div>
                                                    <x-input-label for="attachment_{{ $appointment->id }}"
                                                        value="Anexo (opcional)" class="text-xs" />
                                                    <input id="attachment_{{ $appointment->id }}" name="attachment"
                                                        type="file" class="mt-1 block w-full text-sm text-gray-700" />
                                                    <p class="mt-1 text-xs text-gray-500">Imagens (JPEG, PNG, WEBP, GIF) ou
                                                        PDF at 5 MB.</p>
                                                </div>

                                                <div class="flex gap-2">
                                                    <button type="submit"
                                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                        </svg>
                                                        Enviar Agora
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    @else
                                        <div class="text-xs text-red-700 bg-red-100 px-3 py-2 rounded">
                                            Apenas administradores podem enviar lembretes.
                                        </div>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            @endif

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Proximos compromissos</h3>

                    @if ($upcoming->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum compromisso futuro cadastrado.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs uppercase text-gray-500">
                                        <th class="px-3 py-2">Titulo</th>
                                        <th class="px-3 py-2">Data</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">WhatsApp</th>
                                        <th class="px-3 py-2">Contato</th>
                                        <th class="px-3 py-2 text-right">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                    @foreach ($upcoming as $appointment)
                                        <tr>
                                            <td class="px-3 py-2">
                                                <p class="font-semibold text-gray-900">{{ $appointment->titulo }}</p>
                                                @if ($appointment->descricao)
                                                    <p class="text-xs text-gray-500">
                                                        {{ Str::limit($appointment->descricao, 80) }}</p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <p class="font-medium">
                                                    {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                                </p>
                                                @if ($appointment->fim)
                                                    <p class="text-xs text-gray-500">ate
                                                        {{ $appointment->fim->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                                    </p>
                                                @elseif ($appointment->dia_inteiro)
                                                    <p class="text-xs text-gray-500">Dia inteiro</p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                @php
                                                    $statusKey = $appointment->status ?? 'pendente';
                                                    $statusBadge =
                                                        $statusStyles[$statusKey] ?? 'bg-gray-100 text-gray-700';
                                                    $statusLabel = $statusLabels[$statusKey] ?? Str::title($statusKey);
                                                @endphp
                                                <span
                                                    class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $statusBadge }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                @if ($appointment->notificar_whatsapp)
                                                    @php
                                                        $statusLembrete = $appointment->status_lembrete ?? 'pendente';
                                                        $badgeClasses = [
                                                            'pendente' =>
                                                                'bg-yellow-100 text-yellow-800 border-yellow-300',
                                                            'enviado' => 'bg-green-100 text-green-800 border-green-300',
                                                            'falhou' => 'bg-red-100 text-red-800 border-red-300',
                                                        ];
                                                        $badgeLabels = [
                                                            'pendente' => 'Aguardando',
                                                            'enviado' => 'Enviado',
                                                            'falhou' => 'Falhou',
                                                        ];
                                                        $badgeClass =
                                                            $badgeClasses[$statusLembrete] ??
                                                            'bg-gray-100 text-gray-800';
                                                        $badgeLabel = $badgeLabels[$statusLembrete] ?? $statusLembrete;
                                                    @endphp
                                                    <div class="flex flex-col gap-1">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded border text-xs font-semibold {{ $badgeClass }}">
                                                            {{ $badgeLabel }}
                                                        </span>
                                                        @if ($appointment->lembrar_em && $statusLembrete === 'pendente')
                                                            <p class="text-xs text-gray-500">
                                                                {{ $appointment->lembrar_em->timezone(config('app.timezone'))->format('d/m H:i') }}
                                                            </p>
                                                        @endif
                                                        @if ($appointment->lembrete_enviado_em && $statusLembrete === 'enviado')
                                                            <p class="text-xs text-green-600">
                                                                {{ $appointment->lembrete_enviado_em->timezone(config('app.timezone'))->format('d/m H:i') }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded border bg-gray-50 text-gray-500 border-gray-200 text-xs">
                                                        Desativado
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ $appointment->contact_name ?? 'Sem cadastro' }}
                                                </p>
                                                @if ($appointment->contact_phone)
                                                    <p class="text-xs font-mono text-gray-700">
                                                        {{ $appointment->contact_phone }}
                                                    </p>
                                                @else
                                                    <span class="text-xs text-gray-400">Sem telefone</span>
                                                @endif
                                                @if ($appointment->whatsapp_mensagem)
                                                    <p class="text-xs text-gray-500 mt-1 italic">
                                                        "{{ Str::limit($appointment->whatsapp_mensagem, 40) }}"
                                                    </p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex justify-end gap-2 flex-wrap">
                                                    <form method="POST"
                                                        action="{{ route('agenda.status', $appointment) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded {{ $appointment->isCompleted() ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 border border-amber-300' : 'bg-green-100 text-green-700 hover:bg-green-200 border border-green-300' }}">
                                                            @if ($appointment->isCompleted())
                                                                <svg class="w-3 h-3 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                Reabrir
                                                            @else
                                                                <svg class="w-3 h-3 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                Concluir
                                                            @endif
                                                        </button>
                                                    </form>

                                                    <a href="{{ route('agenda.edit', $appointment) }}"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 border border-blue-300 rounded text-xs font-medium hover:bg-blue-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Editar
                                                    </a>

                                                    @if (auth()->user()->isAdmin())
                                                        <form method="POST"
                                                            action="{{ route('agenda.destroy', $appointment) }}"
                                                            onsubmit="return confirm('Tem certeza que deseja excluir este compromisso?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 border border-red-300 rounded text-xs font-medium hover:bg-red-200">
                                                                <svg class="w-3 h-3 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                                Excluir
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Seo Concludos com Filtros --}}
            <div class="bg-white shadow sm:rounded-lg border-l-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-bold text-gray-900">Concludos</h3>
                            <span class="ml-2 bg-green-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                                {{ $concluidos->count() }}
                            </span>
                        </div>
                    </div>

                    {{-- Abas de Filtro --}}
                    <div class="mb-4 border-b border-gray-200">
                        <nav class="flex space-x-2" aria-label="Filtros de perodo">
                            <button data-filter="hoje" data-section="concluidos"
                                class="filter-tab-concluidos px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-green-500 hover:text-green-600 transition">
                                Hoje
                            </button>
                            <button data-filter="semana" data-section="concluidos"
                                class="filter-tab-concluidos px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-green-500 hover:text-green-600 transition">
                                Esta Semana
                            </button>
                            <button data-filter="mes" data-section="concluidos"
                                class="filter-tab-concluidos px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-green-500 hover:text-green-600 transition">
                                Este Mês
                            </button>
                            <button data-filter="todos" data-section="concluidos"
                                class="filter-tab-concluidos active px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-green-600 text-green-600 transition">
                                Todos
                            </button>
                        </nav>
                    </div>

                    @if ($concluidos->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum compromisso concludo.</p>
                    @else
                        <ul class="divide-y divide-gray-200 text-sm text-gray-700" id="concluidos-list">
                            @foreach ($concluidos as $appointment)
                                <li class="py-3 appointment-item"
                                    data-date="{{ $appointment->inicio->format('Y-m-d') }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ $appointment->titulo }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                            </p>
                                            @if ($appointment->descricao)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    {{ Str::limit($appointment->descricao, 60) }}</p>
                                            @endif
                                            @if ($appointment->contact_name)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    <strong>Cliente:</strong> {{ $appointment->contact_name }}
                                                </p>
                                            @endif
                                            @if ($appointment->contact_phone)
                                                <p class="text-xs text-gray-500">
                                                    <strong>WhatsApp:</strong> {{ $appointment->contact_phone }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-green-100 text-green-800 border border-green-300">
                                                Concludo
                                            </span>
                                            <form method="POST"
                                                action="{{ route('agenda.update-status', ['appointment' => $appointment->id, 'status' => 'pendente']) }}"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-300 transition">
                                                    Pendente
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('agenda.update-status', ['appointment' => $appointment->id, 'status' => 'cancelado']) }}"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300 hover:bg-red-50 hover:text-red-700 hover:border-red-300 transition">
                                                    Cancelado
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Seo Pendentes --}}
            <div class="bg-white shadow sm:rounded-lg border-l-4 border-amber-500">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-amber-600 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-bold text-gray-900">Pendentes</h3>
                        <span class="ml-2 bg-amber-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                            {{ $pendentes->count() }}
                        </span>
                    </div>
                    @if ($pendentes->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum compromisso pendente.</p>
                    @else
                        <ul class="divide-y divide-gray-200 text-sm text-gray-700">
                            @foreach ($pendentes as $appointment)
                                <li class="py-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ $appointment->titulo }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                            </p>
                                            @if ($appointment->descricao)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    {{ Str::limit($appointment->descricao, 60) }}</p>
                                            @endif
                                            @if ($appointment->contact_name)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    <strong>Cliente:</strong> {{ $appointment->contact_name }}
                                                </p>
                                            @endif
                                            @if ($appointment->contact_phone)
                                                <p class="text-xs text-gray-500">
                                                    <strong>WhatsApp:</strong> {{ $appointment->contact_phone }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                                                Pendente
                                            </span>
                                            <form method="POST"
                                                action="{{ route('agenda.update-status', ['appointment' => $appointment->id, 'status' => 'concluido']) }}"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300 hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition">
                                                    Concludo
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('agenda.update-status', ['appointment' => $appointment->id, 'status' => 'cancelado']) }}"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300 hover:bg-red-50 hover:text-red-700 hover:border-red-300 transition">
                                                    Cancelado
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Seo Cancelados com Filtros --}}
            <div class="bg-white shadow sm:rounded-lg border-l-4 border-red-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-bold text-gray-900">Cancelados</h3>
                            <span class="ml-2 bg-red-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                                {{ $cancelados->count() }}
                            </span>
                        </div>
                    </div>

                    {{-- Abas de Filtro --}}
                    <div class="mb-4 border-b border-gray-200">
                        <nav class="flex space-x-2" aria-label="Filtros de perodo">
                            <button data-filter="hoje" data-section="cancelados"
                                class="filter-tab-cancelados px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-red-500 hover:text-red-600 transition">
                                Hoje
                            </button>
                            <button data-filter="semana" data-section="cancelados"
                                class="filter-tab-cancelados px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-red-500 hover:text-red-600 transition">
                                Esta Semana
                            </button>
                            <button data-filter="mes" data-section="cancelados"
                                class="filter-tab-cancelados px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-red-500 hover:text-red-600 transition">
                                Este Mês
                            </button>
                            <button data-filter="todos" data-section="cancelados"
                                class="filter-tab-cancelados active px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 border-red-600 text-red-600 transition">
                                Todos
                            </button>
                        </nav>
                    </div>

                    @if ($cancelados->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum compromisso cancelado.</p>
                    @else
                        <ul class="divide-y divide-gray-200 text-sm text-gray-700" id="cancelados-list">
                            @foreach ($cancelados as $appointment)
                                <li class="py-3 appointment-item"
                                    data-date="{{ $appointment->inicio->format('Y-m-d') }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ $appointment->titulo }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                            </p>
                                            @if ($appointment->descricao)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    {{ Str::limit($appointment->descricao, 60) }}</p>
                                            @endif
                                            @if ($appointment->contact_name)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    <strong>Cliente:</strong> {{ $appointment->contact_name }}
                                                </p>
                                            @endif
                                            @if ($appointment->contact_phone)
                                                <p class="text-xs text-gray-500">
                                                    <strong>WhatsApp:</strong> {{ $appointment->contact_phone }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-red-100 text-red-800 border border-red-300">
                                                Cancelado
                                            </span>
                                            <form method="POST"
                                                action="{{ route('agenda.update-status', ['appointment' => $appointment->id, 'status' => 'pendente']) }}"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-300 transition">
                                                    Pendente
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('agenda.update-status', ['appointment' => $appointment->id, 'status' => 'concluido']) }}"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300 hover:bg-green-50 hover:text-green-700 hover:border-green-300 transition">
                                                    Concluido
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Historico recente</h3>
                    @if ($recent->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum registro recente.</p>
                    @else
                        <ul class="divide-y divide-gray-200 text-sm text-gray-700">
                            @foreach ($recent as $appointment)
                                <li class="py-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ $appointment->titulo }}</p>
                                            <p class="text-xs text-gray-500">Realizado em
                                                {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                            </p>
                                            @if ($appointment->descricao)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    {{ Str::limit($appointment->descricao, 60) }}</p>
                                            @endif
                                            @if ($appointment->contact_name)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    <strong>Cliente:</strong> {{ $appointment->contact_name }}
                                                </p>
                                            @endif
                                            @if ($appointment->contact_phone)
                                                <p class="text-xs text-gray-500">
                                                    <strong>WhatsApp:</strong> {{ $appointment->contact_phone }}
                                                </p>
                                            @endif
                                        </div>

                                        @php
                                            $currentStatus = $appointment->status ?? 'pendente';
                                            $statusButtons = [
                                                'pendente' => [
                                                    'label' => 'Pendente',
                                                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                                    'color' => 'amber',
                                                ],
                                                'concluido' => [
                                                    'label' => 'Concludo',
                                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                                    'color' => 'green',
                                                ],
                                                'cancelado' => [
                                                    'label' => 'Cancelado',
                                                    'icon' =>
                                                        'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                                    'color' => 'red',
                                                ],
                                            ];
                                        @endphp

                                        <div class="flex items-center gap-2 flex-wrap">
                                            @foreach ($statusButtons as $statusKey => $config)
                                                @if ($currentStatus === $statusKey)
                                                    <span
                                                        class="inline-flex items-center px-3 py-1.5 rounded text-xs font-semibold bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 border border-{{ $config['color'] }}-300">
                                                        <svg class="w-3 h-3 mr-1" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="{{ $config['icon'] }}" />
                                                        </svg>
                                                        {{ $config['label'] }}
                                                    </span>
                                                @else
                                                    <form method="POST"
                                                        action="{{ route('agenda.update-status', ['appointment' => $appointment->id, 'status' => $statusKey]) }}"
                                                        class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-2.5 py-1.5 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300 hover:bg-{{ $config['color'] }}-50 hover:text-{{ $config['color'] }}-700 hover:border-{{ $config['color'] }}-300 transition">
                                                            <svg class="w-3 h-3 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="{{ $config['icon'] }}" />
                                                            </svg>
                                                            {{ $config['label'] }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <div id="appointment-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60" data-modal-close></div>
        <div class="relative z-10 w-full max-w-xl bg-white rounded-2xl shadow-2xl p-6 sm:p-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold text-slate-900" data-modal-title>Compromisso</h2>
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600">
                        <span
                            class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold"
                            data-modal-status></span>
                        <span data-modal-datetime></span>
                    </div>
                </div>
                <button type="button" class="text-slate-500 hover:text-slate-700 transition-colors"
                    aria-label="Fechar modal" data-modal-close>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 text-sm text-slate-700">
                <div>
                    <p class="uppercase text-xs font-semibold text-slate-500">Descrição</p>
                    <p class="mt-1 whitespace-pre-line" data-modal-description>-</p>
                </div>
                <div>
                    <p class="uppercase text-xs font-semibold text-slate-500">Lembrete via WhatsApp</p>
                    <p class="mt-1" data-modal-whatsapp>-</p>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <button type="button"
                    class="w-full sm:w-auto inline-flex justify-center rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors"
                    data-modal-close>
                    Fechar
                </button>
                <a href="#"
                    class="w-full sm:w-auto inline-flex justify-center rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors"
                    data-modal-edit>
                    Editar compromisso
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log(' Sistema de agenda carregado.');

            const templateLimit = {{ $quickMessageTemplateLimit ?? 5 }};
            let quickTemplates = @json($quickTemplateOptions ?? []);
            console.log('Quick templates carregados:', quickTemplates);
            console.log('Total de templates:', quickTemplates.length);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const quickTemplateContainers = Array.from(document.querySelectorAll('[data-quick-template-controls]'))
                .filter((container) => container.dataset.qtBound !== '1');

            console.log('Containers de templates encontrados:', quickTemplateContainers.length);

            if (quickTemplateContainers.length) {
                const parseJson = async (response) => {
                    const contentType = response.headers.get('content-type') ?? '';
                    if (contentType.includes('application/json')) {
                        try {
                            return await response.json();
                        } catch (_error) {
                            return null;
                        }
                    }

                    return null;
                };

                const showFeedback = (container, message, type = 'success') => {
                    const feedback = container.querySelector('[data-template-feedback]');
                    if (!feedback) {
                        return;
                    }

                    feedback.textContent = message;
                    feedback.classList.remove('hidden', 'text-emerald-600', 'text-red-600');
                    feedback.classList.add(type === 'error' ? 'text-red-600' : 'text-emerald-600');
                    feedback.classList.remove('hidden');

                    if (feedback._timeoutId) {
                        window.clearTimeout(feedback._timeoutId);
                    }

                    feedback._timeoutId = window.setTimeout(() => {
                        feedback.classList.add('hidden');
                        feedback._timeoutId = null;
                    }, 4000);
                };

                                                                                    const getTextarea = (container) => {
                                    const targetId = container.dataset.target;
                                    return targetId ? document.getElementById(targetId) : null;
                                };

                                const findTemplateById = (templateId) => {
                                    return quickTemplates.find((template) => String(template.id) === String(templateId));
                                };

                                const setEditingState = (container, templateId = null) => {
                                    const saveButton = container.querySelector('[data-action="save-template"]');

                                    if (templateId) {
                                        container.dataset.editingTemplateId = String(templateId);
                                        if (saveButton) {
                                            saveButton.dataset.mode = 'update';
                                            saveButton.disabled = false;
                                            saveButton.classList.remove('opacity-60');
                                            saveButton.removeAttribute('title');
                                        }
                                    } else {
                                        delete container.dataset.editingTemplateId;
                                        if (saveButton) {
                                            delete saveButton.dataset.mode;
                                        }
                                    }
                                };

                                const applyMessageToTextarea = (container, message, successMessage = null, targetId = null) => {
                                    const textarea = targetId
                                        ? document.getElementById(targetId)
                                        : getTextarea(container);

                                    if (!textarea) {
                                        showFeedback(container, 'Campo de mensagem nao encontrado.', 'error');
                                        return false;
                                    }

                                    if (!message) {
                                        showFeedback(container, 'Mensagem salva nao encontrada.', 'error');
                                        return false;
                                    }

                                    textarea.value = message;
                                    textarea.focus();
                                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                                    textarea.dispatchEvent(new Event('change', { bubbles: true }));

                                    if (successMessage) {
                                        showFeedback(container, successMessage, 'success');
                                    }

                                    return true;
                                };

                                const createActionButton = (classes, action, iconSvg, label, template) => {
                                    const button = document.createElement('button');
                                    button.type = 'button';
                                    button.className = classes;
                                    button.dataset.action = action;
                                    button.dataset.templateId = String(template.id);
                                    button.innerHTML = `${iconSvg} ${label}`;

                                    if (action === 'apply-template' || action === 'edit-template') {
                                        button.dataset.message = template.message;
                                    }

                                    return button;
                                };

                                const renderTemplateList = (container) => {
                                    const list = container.querySelector('[data-template-list]');
                                    if (!list) {
                                        return;
                                    }

                                    // Renderizar apenas uma vez globalmente
                                    if (list.dataset.rendered === '1') {
                                        return;
                                    }
                                    list.dataset.rendered = '1';

                                    const selectedId = container.dataset.selectedTemplateId ?? null;

                                    list.innerHTML = '';
                                    list.className = 'flex gap-3 overflow-x-auto pb-2';

                                    if (!quickTemplates.length) {
                                        delete container.dataset.selectedTemplateId;
                                        return;
                                    }

                                    const useIcon = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 012 2v12a2 2 0 01-2 2h-2m-8 0H6a2 2 0 01-2-2V6a2 2 0 012-2h2m2-1v18m4-18v18"></path></svg>';
                                    const editIcon = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>';
                                    const deleteIcon = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

                                    quickTemplates.forEach((template) => {
                                        const wrapper = document.createElement('div');
                                        wrapper.className = 'flex-shrink-0 w-64 rounded-lg border border-indigo-200 bg-white shadow-sm p-4 cursor-pointer hover:border-indigo-400 hover:shadow-md transition-all';
                                        wrapper.dataset.templateId = String(template.id);
                                        wrapper.dataset.templateMessage = template.message;
                                        wrapper.dataset.action = 'apply-template';
                                        wrapper.dataset.message = template.message;
                                        if (container.dataset.target) {
                                            wrapper.dataset.target = container.dataset.target;
                                        }

                                        const contentContainer = document.createElement('div');
                                        contentContainer.className = 'space-y-3';

                                        const messageParagraph = document.createElement('p');
                                        messageParagraph.className = 'text-sm text-slate-800 whitespace-pre-line leading-relaxed line-clamp-3';
                                        messageParagraph.setAttribute('data-template-message-text', '');
                                        messageParagraph.textContent = template.message;

                                        const actions = document.createElement('div');
                                        actions.className = 'flex flex-wrap gap-2';

                                        const editButton = createActionButton(
                                            'inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1',
                                            'edit-template',
                                            editIcon,
                                            'Editar',
                                            template
                                        );
                                        editButton.setAttribute('onclick', 'event.stopPropagation()');

                                        const deleteButton = createActionButton(
                                            'inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-medium text-rose-600 transition hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1',
                                            'delete-template',
                                            deleteIcon,
                                            'Excluir',
                                            template
                                        );
                                        deleteButton.setAttribute('onclick', 'event.stopPropagation()');

                                        if (container.dataset.target) {
                                            editButton.dataset.target = container.dataset.target;
                                        }

                                        actions.appendChild(editButton);
                                        actions.appendChild(deleteButton);

                                        contentContainer.appendChild(messageParagraph);
                                        contentContainer.appendChild(actions);
                                        wrapper.appendChild(contentContainer);
                                        list.appendChild(wrapper);
                                    });

                                };

                                const updateTemplateControls = () => {
                                    quickTemplateContainers.forEach((container) => {
                                        const select = container.querySelector('.quick-template-select');
                                        const applyButton = container.querySelector('[data-action="apply-template"]');
                                        const deleteButton = container.querySelector('[data-action="delete-template"]');
                                        const saveButton = container.querySelector('[data-action="save-template"]');
                                        const countLabel = container.querySelector('[data-template-count]');
                                        const emptyMessage = container.querySelector('[data-templates-empty]');
                                        const limit = Number(container.dataset.limit || templateLimit);
                                        const isEditing = Boolean(container.dataset.editingTemplateId);

                                        if (countLabel) {
                                            countLabel.textContent = `${quickTemplates.length}/${limit} mensagens salvas`;
                                        }

                                        if (emptyMessage) {
                                            emptyMessage.style.display = quickTemplates.length === 0 ? '' : 'none';
                                        }

                                        if (select) {
                                            const previousValue = select.value;
                                            select.innerHTML = '';

                                            const placeholder = document.createElement('option');
                                            placeholder.value = '';
                                            placeholder.textContent = quickTemplates.length
                                                ? 'Selecione uma mensagem salva'
                                                : 'Nenhuma mensagem salva';
                                            select.appendChild(placeholder);

                                            quickTemplates.forEach((template) => {
                                                const option = document.createElement('option');
                                                option.value = String(template.id);
                                                option.textContent = template.preview;
                                                option.dataset.message = template.message;
                                                select.appendChild(option);
                                            });

                                            if (previousValue && quickTemplates.some((template) => String(template.id) === previousValue)) {
                                                select.value = previousValue;
                                            }
                                        }

                                        const hasTemplates = quickTemplates.length > 0;

                                        if (applyButton) {
                                            applyButton.disabled = !hasTemplates;
                                        }

                                        if (deleteButton) {
                                            deleteButton.disabled = !hasTemplates;
                                        }

                                        if (saveButton) {
                                            const limitReached = !isEditing && quickTemplates.length >= limit;
                                            saveButton.disabled = limitReached;
                                            saveButton.classList.toggle('opacity-60', limitReached);

                                            if (limitReached) {
                                                saveButton.setAttribute(
                                                    'title',
                                                    'Limite atingido. Exclua uma mensagem salva para adicionar outra.'
                                                );
                                            } else {
                                                saveButton.removeAttribute('title');
                                            }
                                        }

                                        renderTemplateList(container);
                                    });
                                };

                                updateTemplateControls();

                                quickTemplateContainers.forEach((container) => {
                                    container.addEventListener('click', async (event) => {
                                        const target = event.target;

                    let trigger = target;
                    while (trigger && trigger !== container) {
                        if (typeof trigger.matches === 'function' && trigger.matches('button[data-action], div[data-action="apply-template"]')) {
                            break;
                        }
                        trigger = trigger.parentElement || trigger.parentNode || null;
                    }

                    if (!trigger || trigger === container || typeof trigger.matches !== 'function' || !trigger.matches('button[data-action], div[data-action="apply-template"]')) {
                        return;
                    }

                                        const action = trigger.dataset.action;
                                        const saveUrl = container.dataset.saveUrl;
                                        const updateUrlTemplate = container.dataset.updateUrlTemplate;
                                        const deleteUrlTemplate = container.dataset.deleteUrlTemplate;

                                        if (action === 'apply-template') {
                                            event.preventDefault();

                                            const templateId = trigger.dataset.templateId;
            const message = trigger.dataset.message ?? findTemplateById(templateId)?.message ?? '';
            const targetId = trigger.dataset.target || container.dataset.target;

            if (!applyMessageToTextarea(container, message, 'Mensagem aplicada.', targetId)) {
                return;
            }

                                            container.dataset.selectedTemplateId = templateId ?? '';
                                            setEditingState(container, null);
                                            updateTemplateControls();
                                            return;
                                        }

                                        if (action === 'edit-template') {
                                            event.preventDefault();

                                            const templateId = trigger.dataset.templateId;
            const message = trigger.dataset.message ?? findTemplateById(templateId)?.message ?? '';
            const targetId = trigger.dataset.target || container.dataset.target;

            if (!applyMessageToTextarea(container, message, null, targetId)) {
                return;
            }

                                            container.dataset.selectedTemplateId = templateId ?? '';
                                            setEditingState(container, templateId);
                                            updateTemplateControls();
                                            showFeedback(container, 'Mensagem carregada para edicao. Ajuste e clique em Guardar para atualizar.', 'success');
                                            return;
                                        }

                                        if (action === 'save-template') {
                                            event.preventDefault();

                                            const textarea = getTextarea(container);

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

                                            if (!csrfToken) {
                                                showFeedback(container, 'Token CSRF nao encontrado. Recarregue a pagina.', 'error');
                                                return;
                                            }

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

                                                const payload = await parseJson(response);

                                                if (response.ok) {
                                                    quickTemplates = Array.isArray(payload?.templates) ? payload.templates : [];
                                                    setEditingState(container, null);
                                                    if (!isEditing && quickTemplates.length) {
                                                        container.dataset.selectedTemplateId = String(quickTemplates[0].id);
                                                    }
                                                    updateTemplateControls();
                                                    showFeedback(
                                                        container,
                                                        payload?.message ?? (isEditing ? 'Mensagem atualizada.' : 'Mensagem salva.'),
                                                        'success'
                                                    );
                                                } else {
                                                    const errorMessage = payload?.message ?? 'Nao foi possivel salvar a mensagem.';
                                                    showFeedback(container, errorMessage, 'error');
                                                }
                                            } catch (_error) {
                                                showFeedback(container, 'Erro ao salvar mensagem. Tente novamente.', 'error');
                                            } finally {
                                                trigger.disabled = false;
                                            }

                                            return;
                                        }

                                        if (action === 'delete-template') {
                                            event.preventDefault();

                                            const templateId = trigger.dataset.templateId ??
                                                trigger.closest('[data-template-id]')?.dataset.templateId;

                                            if (!templateId) {
                                                showFeedback(container, 'Mensagem salva nao encontrada.', 'error');
                                                return;
                                            }

                                            if (!deleteUrlTemplate) {
                                                showFeedback(container, 'Nao foi possivel encontrar a rota para exclusao.', 'error');
                                                return;
                                            }

                                            if (!csrfToken) {
                                                showFeedback(container, 'Token CSRF nao encontrado. Recarregue a pagina.', 'error');
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

                                                const payload = await parseJson(response);

                                                if (response.ok) {
                                                    quickTemplates = Array.isArray(payload?.templates) ? payload.templates : [];
                                                    if (container.dataset.editingTemplateId === String(templateId)) {
                                                        setEditingState(container, null);
                                                    }
                                                    if (container.dataset.selectedTemplateId === String(templateId)) {
                                                        delete container.dataset.selectedTemplateId;
                                                    }
                                                    updateTemplateControls();
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
                                        }
                                    });
                                });
            // Sistema de filtros por perodo
            function setupPeriodFilters(section, borderColor, textColor) {
                const tabs = document.querySelectorAll(`.filter-tab-${section}`);
                const list = document.getElementById(`${section}-list`);

                if (!list) return;

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        const filter = tab.dataset.filter;

                        // Atualiza estilo das abas
                        tabs.forEach(t => {
                            t.classList.remove('active', `border-${borderColor}`,
                                `text-${textColor}`);
                            t.classList.add('border-transparent');
                        });
                        tab.classList.add('active', `border-${borderColor}`, `text-${textColor}`);
                        tab.classList.remove('border-transparent');

                        // Filtra os itens
                        const items = list.querySelectorAll('.appointment-item');
                        const hoje = new Date();
                        hoje.setHours(0, 0, 0, 0);

                        const inicioSemana = new Date(hoje);
                        inicioSemana.setDate(hoje.getDate() - hoje.getDay());

                        const fimSemana = new Date(inicioSemana);
                        fimSemana.setDate(inicioSemana.getDate() + 6);

                        const inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                        const fimMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

                        let visibleCount = 0;

                        items.forEach(item => {
                            const itemDate = new Date(item.dataset.date + 'T00:00:00');
                            let shouldShow = false;

                            switch (filter) {
                                case 'hoje':
                                    shouldShow = itemDate.getTime() === hoje.getTime();
                                    break;
                                case 'semana':
                                    shouldShow = itemDate >= inicioSemana && itemDate <=
                                        fimSemana;
                                    break;
                                case 'mes':
                                    shouldShow = itemDate >= inicioMes && itemDate <=
                                        fimMes;
                                    break;
                                case 'todos':
                                    shouldShow = true;
                                    break;
                            }

                            if (shouldShow) {
                                item.style.display = '';
                                visibleCount++;
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        // Mostra mensagem se no h itens
                        let emptyMessage = list.parentElement.querySelector('.empty-message');
                        if (visibleCount === 0) {
                            if (!emptyMessage) {
                                emptyMessage = document.createElement('p');
                                emptyMessage.className = 'empty-message text-sm text-gray-600 mt-4';
                                emptyMessage.textContent =
                                    'Nenhum compromisso encontrado para este perodo.';
                                list.parentElement.appendChild(emptyMessage);
                            }
                            emptyMessage.style.display = '';
                        } else if (emptyMessage) {
                            emptyMessage.style.display = 'none';
                        }
                    });
                });
            }

            // Configura filtros para concludos e cancelados
            setupPeriodFilters('concluidos', 'green-600', 'green-600');
            setupPeriodFilters('cancelados', 'red-600', 'red-600');
        });
    </script>

</x-app-layout>

