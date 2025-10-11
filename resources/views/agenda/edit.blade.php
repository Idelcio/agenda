<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar compromisso
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $appointment->titulo }}</h3>
                        <p class="text-sm text-gray-600">
                            Início: {{ $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                            @if ($appointment->fim)
                                · Fim: {{ $appointment->fim->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
                            <strong>Ops!</strong>
                            <p class="mt-2 text-sm">Verifique os campos destacados e tente novamente.</p>
                        </div>
                    @endif

                    @include('agenda.partials.form', [
                        'appointment' => $appointment,
                        'defaultWhatsapp' => $appointment->user->whatsapp_number,
                        'submitLabel' => 'Atualizar compromisso',
                        'httpMethod' => 'PUT',
                        'action' => route('agenda.update', $appointment),
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
