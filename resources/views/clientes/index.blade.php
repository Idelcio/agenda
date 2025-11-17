<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Meus Clientes
            </h2>
            <a href="{{ route('clientes.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Novo Cliente
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Botão Histórico / Voltar com estilo Agendoo --}}
            <div class="flex justify-start">
                <a href="{{ route('agenda.index') }}"
                    class="inline-flex items-center mb-4 px-5 py-2.5 bg-white text-indigo-600 border border-indigo-200 rounded-lg shadow hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200 font-semibold text-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Voltar
                </a>
            </div>



            {{-- Estatísticas de Clientes --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="bg-gradient-to-br from-green-50 to-green-100 p-6 shadow-md sm:rounded-lg border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-green-600">Total de Clientes</p>
                            <p class="text-3xl font-bold text-green-900 mt-2">{{ $clientes->total() }}</p>
                        </div>
                        <div class="bg-green-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 shadow-md sm:rounded-lg border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-blue-600">Cadastrados Hoje</p>
                            <p class="text-3xl font-bold text-blue-900 mt-2">
                                {{ $clientes->where('created_at', '>=', now()->startOfDay())->count() }}
                            </p>
                        </div>
                        <div class="bg-blue-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-6 shadow-md sm:rounded-lg border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-emerald-600">Com WhatsApp</p>
                            <p class="text-3xl font-bold text-emerald-900 mt-2">
                                {{ $clientes->where('whatsapp_number', '!=', null)->count() }}
                            </p>
                        </div>
                        <div class="bg-emerald-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabela de Clientes --}}
            <div class="bg-white shadow sm:rounded-lg" x-data="{
                selectedClients: [],
                selectAll: false,
                tipoEnvio: 'texto',
                agendamento: 'imediato',
                toggleAll() {
                    if (this.selectAll) {
                        this.selectedClients = [...document.querySelectorAll('.client-checkbox')].map(cb => parseInt(cb.value));
                    } else {
                        this.selectedClients = [];
                    }
                },
                updateSelectAll() {
                    const checkboxes = document.querySelectorAll('.client-checkbox');
                    this.selectAll = checkboxes.length > 0 && this.selectedClients.length === checkboxes.length;
                },
                openMassModal() {
                    if (this.selectedClients.length === 0) {
                        return;
                    }

                    this.tipoEnvio = 'texto';
                    this.agendamento = 'imediato';

                    if (this.$refs.massMessageForm) {
                        this.$refs.massMessageForm.reset();
                    }

                    this.$refs.massMessageModal.classList.remove('hidden');
                    this.$refs.massMessageModal.classList.add('flex');
                },
                closeMassModal() {
                    this.$refs.massMessageModal.classList.add('hidden');
                    this.$refs.massMessageModal.classList.remove('flex');
                }
            }">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Lista de Clientes</h3>
                        <div class="flex gap-2">
                            <button type="button"
                                    @click="openMassModal()"
                                    :disabled="selectedClients.length === 0"
                                    :class="selectedClients.length > 0 ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                Envio em Massa
                            </button>
                        </div>
                    </div>

                    @if ($clientes->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum cliente cadastrado</h3>
                            <p class="mt-1 text-sm text-gray-500">Comece cadastrando seu primeiro cliente.</p>
                            <div class="mt-6">
                                <a href="{{ route('clientes.create') }}"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Cadastrar Cliente
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs uppercase text-gray-500">
                                        <th class="px-3 py-2 w-12">
                                            <input type="checkbox"
                                                   x-model="selectAll"
                                                   @change="toggleAll()"
                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        </th>
                                        <th class="px-3 py-2">Nome</th>
                                        <th class="px-3 py-2">WhatsApp</th>
                                        <th class="px-3 py-2">Cadastrado em</th>
                                        <th class="px-3 py-2 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                    @foreach ($clientes as $cliente)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3">
                                                @if($cliente->whatsapp_number)
                                                    <input type="checkbox"
                                                           class="client-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                           value="{{ $cliente->id }}"
                                                           x-model="selectedClients"
                                                           @change="updateSelectAll()">
                                                @else
                                                    <span class="text-gray-300" title="Sem WhatsApp">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 font-semibold text-gray-900">{{ $cliente->name }}</td>
                                            <td class="px-3 py-3">
                                                @if ($cliente->whatsapp_number)
                                                    <span class="font-mono text-green-700">
                                                        +{{ $cliente->whatsapp_number }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 text-xs">Não informado</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3">
                                                <span
                                                    class="text-xs text-gray-500">{{ $cliente->created_at->format('d/m/Y H:i') }}</span>
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('clientes.edit', $cliente) }}"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 border border-blue-300 rounded text-xs font-medium hover:bg-blue-200">
                                                        Editar
                                                    </a>

                                                    <form method="POST"
                                                        action="{{ route('clientes.destroy', $cliente) }}"
                                                        onsubmit="return confirm('Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 border border-red-300 rounded text-xs font-medium hover:bg-red-200">
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

                        {{-- Paginação --}}
                        <div class="mt-4">
                            {{ $clientes->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal de Envio em Massa Unificado -->
            <div x-ref="massMessageModal"
                 x-cloak
                 class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur">
                <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl">
                    <form x-ref="massMessageForm"
                          action="{{ route('clientes.send-mass-message') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="client_ids" x-bind:value="JSON.stringify(selectedClients)">

                        <header class="flex items-center justify-between rounded-t-3xl bg-green-600 px-6 py-4 text-white">
                            <div class="flex items-center gap-3 text-lg font-semibold">
                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    </svg>
                                </span>
                                <div class="flex flex-col">
                                    <span>Envio em Massa via WhatsApp</span>
                                    <span class="text-xs font-normal text-green-100">Texto, imagem ou ambos</span>
                                </div>
                            </div>
                            <button type="button"
                                    @click="closeMassModal()"
                                    class="rounded-full bg-white/20 p-2 text-white transition hover:bg-white/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </header>

                        <div class="space-y-6 px-6 py-6">
                            <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="flex-1 text-sm text-blue-800">
                                        <p class="font-semibold mb-1">Como funciona?</p>
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Intervalo automatico de <strong>5 segundos</strong> entre cada cliente</li>
                                            <li>Funciona com texto simples, imagem + texto ou somente imagem/PDF</li>
                                            <li>Disparo executado em segundo plano (queue)</li>
                                            <li>Permite agendar para o dia e hora que voce quiser</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Destinatarios Selecionados: <span x-text="selectedClients.length" class="text-green-600"></span> cliente(s)
                                </label>
                            </div>

                            <div>
                                <label for="mass_title" class="block text-sm font-semibold text-gray-700 mb-2">Titulo da campanha</label>
                                <input type="text"
                                       id="mass_title"
                                       name="titulo"
                                       required
                                       maxlength="150"
                                       placeholder="Ex.: Marketing das quintas-feiras"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de conteudo</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                                           :class="tipoEnvio === 'texto' ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-600'">
                                        <input type="radio" class="sr-only" name="tipo_envio" value="texto" x-model="tipoEnvio">
                                        <p class="text-sm font-semibold">Somente texto</p>
                                        <p class="text-xs">Mensagem simples</p>
                                    </label>
                                    <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                                           :class="tipoEnvio === 'texto_midia' ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-600'">
                                        <input type="radio" class="sr-only" name="tipo_envio" value="texto_midia" x-model="tipoEnvio">
                                        <p class="text-sm font-semibold">Texto + arquivo</p>
                                        <p class="text-xs">Legenda + imagem/PDF</p>
                                    </label>
                                    <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                                           :class="tipoEnvio === 'midia' ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-600'">
                                        <input type="radio" class="sr-only" name="tipo_envio" value="midia" x-model="tipoEnvio">
                                        <p class="text-sm font-semibold">Somente arquivo</p>
                                        <p class="text-xs">Imagem ou PDF</p>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="mass_message" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Mensagem
                                </label>
                                <textarea id="mass_message"
                                          name="mensagem"
                                          rows="4"
                                          maxlength="1000"
                                          x-bind:required="tipoEnvio === 'texto'"
                                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                          placeholder="Digite a mensagem que deseja enviar"></textarea>
                                <p class="mt-1 text-xs text-gray-500"
                                   x-text="tipoEnvio === 'texto' ? 'Obrigatorio para disparos apenas de texto.' : 'Opcional quando houver arquivo.'"></p>
                            </div>

                            <div x-show="tipoEnvio !== 'texto'" x-cloak>
                                <label for="mass_file" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Arquivo (Imagem ou PDF)
                                </label>
                                <input type="file"
                                       id="mass_file"
                                       name="arquivo"
                                       accept=".jpg,.jpeg,.png,.gif,.pdf"
                                       x-bind:required="tipoEnvio !== 'texto'"
                                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-green-500 focus:ring-green-500">
                                <p class="mt-1 text-xs text-gray-500">Formatos aceitos: JPG, PNG, GIF ou PDF (at&eacute; 10MB).</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Quando enviar?</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                                           :class="agendamento === 'imediato' ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-600'">
                                        <input type="radio" class="sr-only" name="agendamento_tipo" value="imediato" x-model="agendamento">
                                        <p class="text-sm font-semibold">Enviar agora</p>
                                        <p class="text-xs">Come&ccedil;a imediatamente</p>
                                    </label>
                                    <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                                           :class="agendamento === 'agendado' ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-600'">
                                        <input type="radio" class="sr-only" name="agendamento_tipo" value="agendado" x-model="agendamento">
                                        <p class="text-sm font-semibold">Agendar envio</p>
                                        <p class="text-xs">Escolha dia e hora</p>
                                    </label>
                                </div>
                                <div class="mt-3" x-show="agendamento === 'agendado'" x-cloak>
                                    <label for="mass_schedule" class="block text-sm font-semibold text-gray-700 mb-2">Data e hora do disparo</label>
                                    <input type="datetime-local"
                                           id="mass_schedule"
                                           name="scheduled_for"
                                           min="{{ now()->format('Y-m-d\TH:i') }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <p class="mt-1 text-xs text-gray-500">Use horario local no formato 24h.</p>
                                </div>
                            </div>
                        </div>

                        <footer class="flex flex-col gap-3 rounded-b-3xl bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-500">
                                Intervalo automatico de 5s por cliente. Selecione os contatos desejados antes de enviar.
                            </p>
                            <div class="flex gap-3 justify-end">
                                <button type="button"
                                        @click="closeMassModal()"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                                    Cancelar
                                </button>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-green-600/30 transition hover:bg-green-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="agendamento === 'agendado' ? 'Agendar envio' : 'Enviar agora'">Enviar agora</span>
                                </button>
                            </div>
                        </footer>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
