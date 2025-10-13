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

            {{-- Estatísticas Principais --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 shadow-md sm:rounded-lg border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-blue-600">Total de Compromissos</p>
                            <p class="text-3xl font-bold text-blue-900 mt-2">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-blue-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 shadow-md sm:rounded-lg border-l-4 border-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-amber-600">Pendentes</p>
                            <p class="text-3xl font-bold text-amber-900 mt-2">{{ $stats['pendentes'] }}</p>
                        </div>
                        <div class="bg-amber-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-6 shadow-md sm:rounded-lg border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-emerald-600">Confirmados</p>
                            <p class="text-3xl font-bold text-emerald-900 mt-2">{{ $stats['confirmados'] }}</p>
                        </div>
                        <div class="bg-emerald-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 shadow-md sm:rounded-lg border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-purple-600">Concluídos</p>
                            <p class="text-3xl font-bold text-purple-900 mt-2">{{ $stats['concluidos'] }}</p>
                        </div>
                        <div class="bg-purple-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estatísticas de Lembretes WhatsApp --}}
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
                            <p class="text-xs uppercase font-semibold text-yellow-600">Aguardando Horário</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $stats['lembretes_pendentes'] }}</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <p class="text-xs uppercase font-semibold text-red-600">Falhas no Envio</p>
                            <p class="text-2xl font-bold text-red-900 mt-1">{{ $stats['lembretes_falharam'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Novo compromisso</h3>
                    <p class="text-sm text-gray-600 mb-4">Preencha as informacoes abaixo para registrar um compromisso
                        na agenda.</p>

                    @include('agenda.partials.form', [
                        'appointment' => null,
                        'defaultWhatsapp' => $defaultWhatsapp,
                        'submitLabel' => 'Salvar compromisso',
                        'action' => route('agenda.store'),
                        'httpMethod' => 'POST',
                    ])
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
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
                                placeholder="Digite a mensagem que deseja enviar">{{ old('mensagem', 'Ola! Esta e uma mensagem de teste do Agenda Digital.') }}</textarea>
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
            </div>

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
                            Estes compromissos atingiram o horário programado. Configure e envie manualmente ou aguarde
                            o envio automático.
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
                                            <p class="text-sm text-gray-700 mt-1">
                                                <strong>Compromisso:</strong>
                                                {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y \à\s H:i') }}
                                            </p>
                                            <p class="text-xs text-gray-600 mt-0.5">
                                                <strong>Lembrete programado:</strong>
                                                {{ $appointment->lembrar_em?->timezone(config('app.timezone'))->format('d/m/Y \à\s H:i') }}
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
                                            {{-- Formulário do lembrete --}}
                                            <form method="POST" action="{{ route('agenda.quick-whatsapp') }}"
                                                class="space-y-3 mt-3 pt-3 border-t border-red-200"
                                                enctype="multipart/form-data"
                                                data-lembrete-hora="{{ $appointment->lembrar_em?->timezone(config('app.timezone'))->format('Y-m-d\TH:i:s') }}">
                                                @csrf

                                                <input type="hidden" name="appointment_id"
                                                    value="{{ $appointment->id }}">

                                                <div>
                                                    <x-input-label for="destinatario_{{ $appointment->id }}"
                                                        value="Número de destino" class="text-xs" />
                                                    <x-text-input id="destinatario_{{ $appointment->id }}"
                                                        name="destinatario" type="text"
                                                        class="mt-1 block w-full text-sm"
                                                        value="{{ old('destinatario', $appointment->whatsapp_numero ?? $appointment->user->whatsapp_number) }}"
                                                        placeholder="+5511999999999" required />
                                                </div>

                                                <div>
                                                    <x-input-label for="mensagem_{{ $appointment->id }}" value="Mensagem"
                                                        class="text-xs" />
                                                    <textarea id="mensagem_{{ $appointment->id }}" name="mensagem" rows="3"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                        placeholder="Digite a mensagem do lembrete" required>{{ old('mensagem', $appointment->whatsapp_mensagem ?? sprintf('Olá! Você tem um agendamento de %s em %s.', $appointment->titulo, $appointment->inicio?->timezone(config('app.timezone'))->format('d/m/Y \à\s H:i'))) }}</textarea>
                                                </div>

                                                <div>
                                                    <x-input-label for="attachment_{{ $appointment->id }}"
                                                        value="Anexo (opcional)" class="text-xs" />
                                                    <input id="attachment_{{ $appointment->id }}" name="attachment"
                                                        type="file" class="mt-1 block w-full text-sm text-gray-700" />
                                                    <p class="mt-1 text-xs text-gray-500">Imagens (JPEG, PNG, WEBP, GIF) ou
                                                        PDF até 5 MB.</p>
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
                                                @if ($appointment->whatsapp_numero || $appointment->user->whatsapp_number)
                                                    <p class="text-xs font-mono text-gray-700">
                                                        {{ $appointment->whatsapp_numero ?? $appointment->user->whatsapp_number }}
                                                    </p>
                                                @else
                                                    <span class="text-xs text-gray-400">-</span>
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

                                                    <form method="POST"
                                                        action="{{ route('agenda.destroy', $appointment) }}"
                                                        onsubmit="return confirm('Tem certeza que deseja excluir este compromisso?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 border border-red-300 rounded text-xs font-medium hover:bg-red-200">
                                                            <svg class="w-3 h-3 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Excluir
                                                        </button>
                                                    </form>
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

            {{-- Seção Concluídos --}}
            <div class="bg-white shadow sm:rounded-lg border-l-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-bold text-gray-900">Concluídos</h3>
                        <span class="ml-2 bg-green-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                            {{ $concluidos->count() }}
                        </span>
                    </div>
                    @if ($concluidos->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum compromisso concluído.</p>
                    @else
                        <ul class="divide-y divide-gray-200 text-sm text-gray-700">
                            @foreach ($concluidos as $appointment)
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
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-green-100 text-green-800 border border-green-300">
                                                Concluído
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

            {{-- Seção Pendentes --}}
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
                                                    Concluído
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

            {{-- Seção Cancelados --}}
            <div class="bg-white shadow sm:rounded-lg border-l-4 border-red-500">
                <div class="p-6">
                    <div class="flex items-center mb-4">
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
                    @if ($cancelados->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum compromisso cancelado.</p>
                    @else
                        <ul class="divide-y divide-gray-200 text-sm text-gray-700">
                            @foreach ($cancelados as $appointment)
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
                                                    Concluído
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
                                                    'label' => 'Concluído',
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


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('✅ Sistema de envio automático iniciado (modo servidor)...');

            const TOKEN = document.querySelector('meta[name="csrf-token"]').content;
            const INTERVALO_MS = 60 * 1000; // 1 minuto

            async function enviarLembretesPendentes() {
                try {
                    const resp = await fetch('{{ route('agenda.lembretes-pendentes') }}');
                    const lembretes = await resp.json();

                    if (!lembretes.length) {
                        console.log('⏸️ Nenhum lembrete pendente.');
                        return;
                    }

                    console.log(`📦 ${lembretes.length} lembrete(s) encontrados.`);

                    for (const lembrete of lembretes) {
                        console.log(`🕐 Enviando lembrete ID=${lembrete.id} (${lembrete.titulo})`);

                        const formData = new FormData();
                        formData.append('_token', TOKEN);
                        formData.append('appointment_id', lembrete.id);
                        formData.append('destinatario', lembrete.whatsapp_numero);
                        formData.append('mensagem', lembrete.whatsapp_mensagem ||
                            `Olá! Você tem um agendamento de ${lembrete.titulo} em ${new Date(lembrete.lembrar_em).toLocaleString('pt-BR')}.`
                        );

                        const res = await fetch('{{ route('agenda.quick-whatsapp') }}', {
                            method: 'POST',
                            body: formData
                        });

                        if (res.ok) {
                            console.log(`✅ Lembrete ${lembrete.id} enviado com sucesso.`);
                        } else {
                            console.warn(`⚠️ Falha ao enviar lembrete ${lembrete.id}.`);
                        }
                    }
                } catch (err) {
                    console.error('❌ Erro ao buscar lembretes:', err);
                }
            }

            enviarLembretesPendentes();
            setInterval(enviarLembretesPendentes, INTERVALO_MS);
        });
    </script>

</x-app-layout>
