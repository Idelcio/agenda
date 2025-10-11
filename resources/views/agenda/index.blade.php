@php use Illuminate\Support\Str; @endphp

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
                    {{ $statusMessage !== $statusKey ? $statusMessage : 'Operação realizada com sucesso.' }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="bg-white p-4 shadow sm:rounded-lg">
                    <p class="text-xs uppercase text-gray-500">Total</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white p-4 shadow sm:rounded-lg">
                    <p class="text-xs uppercase text-gray-500">Pendentes</p>
                    <p class="text-2xl font-semibold text-amber-600">{{ $stats['pendentes'] }}</p>
                </div>
                <div class="bg-white p-4 shadow sm:rounded-lg">
                    <p class="text-xs uppercase text-gray-500">Confirmados</p>
                    <p class="text-2xl font-semibold text-emerald-600">{{ $stats['confirmados'] }}</p>
                </div>
                <div class="bg-white p-4 shadow sm:rounded-lg">
                    <p class="text-xs uppercase text-gray-500">Cancelados</p>
                    <p class="text-2xl font-semibold text-rose-600">{{ $stats['cancelados'] }}</p>
                </div>
                <div class="bg-white p-4 shadow sm:rounded-lg">
                    <p class="text-xs uppercase text-gray-500">Com lembrete</p>
                    <p class="text-2xl font-semibold text-indigo-600">{{ $stats['com_lembrete'] }}</p>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Novo compromisso</h3>
                    <p class="text-sm text-gray-600 mb-4">Preencha as informações abaixo para registrar um compromisso na agenda.</p>

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
                        Envie uma mensagem individual de teste para qualquer numero habilitado no WhatsApp.
                    </p>

                    @error('quick_whatsapp')
                        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <form method="POST" action="{{ route('agenda.quick-whatsapp') }}" class="space-y-4" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-input-label for="destinatario" value="Numero de destino" />
                            <x-text-input
                                id="destinatario"
                                name="destinatario"
                                type="text"
                                class="mt-1 block w-full"
                                value="{{ old('destinatario', $quickMessageDefaults['destinatario']) }}"
                                placeholder="+5511999999999"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('destinatario')" />
                        </div>

                        <div>
                            <x-input-label for="mensagem" value="Mensagem" />
                            <textarea
                                id="mensagem"
                                name="mensagem"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Digite a mensagem que deseja enviar"
                            >{{ old('mensagem', 'Ola! Esta e uma mensagem de teste do Agenda Digital.') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('mensagem')" />
                        </div>

                        <div>
                            <x-input-label for="attachment" value="Anexo (opcional)" />
                            <input
                                id="attachment"
                                name="attachment"
                                type="file"
                                class="mt-1 block w-full text-sm text-gray-700"
                            />
                            <p class="mt-1 text-xs text-gray-500">Aceita imagens (JPEG, PNG, WEBP, GIF) ou PDF de ate 5 MB.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                        </div>

                        <div class="flex items-center gap-2">
                            <x-primary-button>Enviar mensagem</x-primary-button>
                            <span class="text-xs text-gray-500">Necessario configurar a API Brasil e autorizar o numero.</span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Próximos compromissos</h3>

                    @if ($upcoming->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum compromisso futuro cadastrado.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs uppercase text-gray-500">
                                        <th class="px-3 py-2">Título</th>
                                        <th class="px-3 py-2">Data</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">WhatsApp</th>
                                        <th class="px-3 py-2 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                    @foreach ($upcoming as $appointment)
                                        <tr>
                                            <td class="px-3 py-2">
                                                <p class="font-semibold text-gray-900">{{ $appointment->titulo }}</p>
                                                @if ($appointment->descricao)
                                                    <p class="text-xs text-gray-500">{{ Str::limit($appointment->descricao, 80) }}</p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <p class="font-medium">
                                                    {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                                </p>
                                                @if ($appointment->fim)
                                                    <p class="text-xs text-gray-500">até {{ $appointment->fim->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                                @elseif($appointment->dia_inteiro)
                                                    <p class="text-xs text-gray-500">Dia inteiro</p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold
                                                    {{ $appointment->isCompleted() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $appointment->isCompleted() ? 'Concluído' : 'Pendente' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                @if ($appointment->notificar_whatsapp)
                                                    <span class="inline-flex items-center text-xs text-emerald-700">
                                                        ✓ Lembrete
                                                        @if ($appointment->lembrar_em)
                                                            <span class="ml-1 text-gray-500">
                                                                ({{ $appointment->lembrar_em->timezone(config('app.timezone'))->format('d/m H:i') }})
                                                            </span>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400">Desativado</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex justify-end space-x-2">
                                                    <form method="POST" action="{{ route('agenda.status', $appointment) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <x-secondary-button type="submit">
                                                            {{ $appointment->isCompleted() ? 'Reabrir' : 'Concluir' }}
                                                        </x-secondary-button>
                                                    </form>

                                                    <a href="{{ route('agenda.edit', $appointment) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                                        Editar
                                                    </a>

                                                    <form method="POST" action="{{ route('agenda.destroy', $appointment) }}" onsubmit="return confirm('Tem certeza que deseja excluir este compromisso?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-danger-button>Excluir</x-danger-button>
                                                    </form>

                                                    @if ($appointment->notificar_whatsapp)
                                                        <form method="POST" action="{{ route('agenda.reminder', $appointment) }}">
                                                            @csrf
                                                            <x-secondary-button type="submit">Enviar lembrete</x-secondary-button>
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

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Histórico recente</h3>
                    @if ($recent->isEmpty())
                        <p class="text-sm text-gray-600">Nenhum registro recente.</p>
                    @else
                        <ul class="divide-y divide-gray-200 text-sm text-gray-700">
                            @foreach ($recent as $appointment)
                                <li class="py-3 flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $appointment->titulo }}</p>
                                        <p class="text-xs text-gray-500">Realizado em {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <span class="text-xs {{ $appointment->isCompleted() ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $appointment->isCompleted() ? 'Concluído' : 'Pendente' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

