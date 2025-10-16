<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('clientes.index') }}"
                class="inline-flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Cliente
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Editar Informações do Cliente</h3>
                            <p class="text-sm text-gray-600">Atualize as informações do cliente {{ $cliente->name }}.
                            </p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
                            <strong>Ops!</strong>
                            <p class="mt-2 text-sm">Verifique os campos destacados e tente novamente.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('clientes.update', $cliente) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" value="Nome completo *" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $cliente->name)" required autofocus
                                placeholder="Digite o nome completo do cliente" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="whatsapp_number" value="WhatsApp *" />
                            <x-text-input id="whatsapp_number" name="whatsapp_number" type="text"
                                class="mt-1 block w-full" :value="old('whatsapp_number', '+' . $cliente->whatsapp_number)" required
                                placeholder="+5511999999999" />
                            <p class="mt-1 text-xs text-gray-500">
                                Digite com o código do país (+55) + DDD + número. Exemplo: +5511999999999
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('whatsapp_number')" />
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t">
                            <a href="{{ route('clientes.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
