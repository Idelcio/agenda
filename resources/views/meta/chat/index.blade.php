<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat WhatsApp (Meta)') }}
        </h2>
    </x-slot>

    <div class="py-6 h-[calc(100vh-65px)]" x-data="metaChat()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full flex border border-gray-200">

                {{-- Sidebar: Lista de Conversas --}}
                <div class="w-1/3 border-r border-gray-200 flex flex-col bg-gray-50">
                    {{-- Header Sidebar --}}
                    <div
                        class="p-4 bg-white border-b border-gray-200 flex justify-between items-center sticky top-0 md:bg-gray-50 bg-white z-10">
                        <h3 class="font-bold text-gray-700 text-lg">Conversas</h3>
                        <button class="text-blue-600 hover:bg-blue-50 p-2 rounded-full transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="p-3 bg-white border-b border-gray-100">
                        <div class="relative">
                            <input type="text" placeholder="Buscar conversa..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-gray-50">
                            <span class="absolute left-3 top-2.5 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Lista Scrollável --}}
                    <div class="flex-1 overflow-y-auto">
                        @foreach($conversations as $chat)
                            <div @click="selectChat({{ json_encode($chat) }})"
                                class="p-4 border-b border-gray-100 cursor-pointer hover:bg-white transition flex gap-3 relative"
                                :class="activeChat && activeChat.id === {{ $chat['id'] }} ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''">

                                {{-- Avatar --}}
                                <div class="flex-shrink-0 relative">
                                    <div
                                        class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold text-lg">
                                        {{ substr($chat['name'], 0, 1) }}
                                    </div>
                                    @if($chat['window_open'])
                                        <div class="absolute -bottom-1 -right-1 bg-green-500 border-2 border-white w-4 h-4 rounded-full"
                                            title="Janela de 24h Aberta"></div>
                                    @else
                                        <div class="absolute -bottom-1 -right-1 bg-gray-400 border-2 border-white w-4 h-4 rounded-full"
                                            title="Janela Fechada"></div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline mb-1">
                                        <h4 class="font-semibold text-gray-900 truncate">{{ $chat['name'] }}</h4>
                                        <span
                                            class="text-xs text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($chat['last_time'])->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 truncate flex items-center gap-1">
                                        @if(!$chat['unread'])
                                            <span class="text-blue-500">✓✓</span>
                                        @endif
                                        {{ $chat['last_message'] }}
                                    </p>
                                </div>

                                {{-- Badge Unread --}}
                                @if($chat['unread'] > 0)
                                    <div
                                        class="absolute right-4 top-1/2 mt-2 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        {{ $chat['unread'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Área Central: Bate-Papo --}}
                <div class="flex-1 flex flex-col bg-[#efeae2]">

                    {{-- Placeholder State --}}
                    <div x-show="!activeChat"
                        class="flex-1 flex flex-col items-center justify-center text-center p-8 text-gray-500">
                        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-gray-800 mb-2">WhatsApp Web (Meta)</h3>
                        <p class="max-w-md">Selecione uma conversa ao lado para começar o atendimento. Lembre-se que
                            você tem 24h para responder mensagens de clientes gratuitamente.</p>
                        <div class="mt-8 text-xs text-gray-400 flex items-center gap-2">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-1.07 3.97-2.9 5.07z" />
                            </svg>
                            Conectado via Meta Cloud API
                        </div>
                    </div>

                    {{-- Chat Ativo --}}
                    <div x-show="activeChat" x-cloak class="flex-1 flex flex-col h-full">

                        {{-- Header Chat --}}
                        <div
                            class="bg-gray-100 border-b border-gray-300 p-3 flex justify-between items-center shadow-sm z-10">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold">
                                    <span x-text="activeChat ? activeChat.name.substring(0,1) : ''"></span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900" x-text="activeChat ? activeChat.name : ''">
                                    </h3>
                                    <div class="text-xs flex items-center gap-1">
                                        <template x-if="activeChat && activeChat.window_open">
                                            <span class="text-green-600 font-medium flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-green-500 block"></span>
                                                Janela aberta (24h)
                                            </span>
                                        </template>
                                        <template x-if="activeChat && !activeChat.window_open">
                                            <span class="text-orange-600 font-medium flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-orange-500 block"></span>
                                                Janela fechada
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="text-gray-500 hover:bg-gray-200 p-2 rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Area de Mensagens --}}
                        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#efeae2] bg-opacity-50"
                            x-ref="messagesContainer">
                            <template x-if="isLoadingMessages">
                                <div class="flex justify-center p-4">
                                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </template>

                            <template x-for="msg in messages" :key="msg.id || msg.tempId">
                                <div class="flex" :class="msg.direction === 'saida' ? 'justify-end' : 'justify-start'">
                                    <div class="rounded-lg py-2 px-3 max-w-[70%] shadow-sm text-sm text-gray-800 relative"
                                        :class="msg.direction === 'saida' ? 'bg-[#d9fdd3] rounded-tr-none' : 'bg-white rounded-tl-none'">
                                        <p x-text="msg.content" class="whitespace-pre-wrap"></p>
                                        <span
                                            class="text-[10px] block text-right mt-1 flex items-center justify-end gap-1"
                                            :class="msg.direction === 'saida' ? 'text-gray-500' : 'text-gray-400'">
                                            <span x-text="msg.time"></span>
                                            <template x-if="msg.direction === 'saida'">
                                                <span class="text-blue-500">✓✓</span>
                                            </template>
                                        </span>
                                    </div>
                                </div>
                            </template>

                            {{-- Aviso de Janela Fechada --}}
                            <template x-if="activeChat && !activeChat.window_open">
                                <div class="flex justify-center my-4">
                                    <div
                                        class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg text-xs font-medium shadow-sm border border-yellow-200 flex items-center gap-2 max-w-sm text-center">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>A janela de 24h fechou. Para continuar, envie um Template pago.</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Input Area --}}
                        <div class="bg-gray-100 p-3 border-t border-gray-300">

                            {{-- Cenário 1: Janela Aberta (Input Livre) --}}
                            <div x-show="activeChat && activeChat.window_open" class="flex gap-2 items-end">
                                <button class="p-2 text-gray-500 hover:bg-gray-200 rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                </button>
                                <div
                                    class="flex-1 bg-white rounded-lg border border-gray-300 shadow-sm flex items-center">
                                    <textarea x-model="newMessage"
                                        @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()" rows="1"
                                        placeholder="Digite uma mensagem..."
                                        class="w-full border-0 focus:ring-0 rounded-lg resize-none py-3 px-4 text-sm bg-transparent"></textarea>
                                </div>
                                <button @click="sendMessage()"
                                    class="p-3 bg-[#00a884] text-white rounded-full hover:bg-[#008f6f] shadow-md transition transform active:scale-95">
                                    <svg class="w-5 h-5 translate-x-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Cenário 2: Janela Fechada (Botão Template) --}}
                            <div x-show="activeChat && !activeChat.window_open" class="flex justify-center py-2">
                                <button
                                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full font-semibold shadow-md transition transform hover:scale-105">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    Selecionar Template (Reabrir Conversa)
                                </button>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('metaChat', () => ({
                activeChat: null,
                newMessage: '',
                messages: [],
                isLoadingMessages: false,

                selectChat(chat) {
                    this.activeChat = chat;
                    this.messages = [];
                    this.isLoadingMessages = true;
                    console.log('Selecionando chat:', chat.phone);

                    fetch(`/meta/chat/${chat.phone}/messages`)
                        .then(r => r.json())
                        .then(data => {
                            this.messages = data.messages;
                            this.scrollToBottom();
                        })
                        .catch(err => console.error('Erro ao carregar mensagens:', err))
                        .finally(() => this.isLoadingMessages = false);
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                },

                sendMessage() {
                    const text = this.newMessage.trim();
                    if (!text) return;

                    const recipientPhone = this.activeChat.phone;
                    console.log('Enviando mensagem:', text, 'para', recipientPhone);

                    // Estado de carregamento básico (opcional)
                    const tempId = Date.now();

                    // Chamada ao Backend
                    fetch('{{ route('meta.chat.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            phone: recipientPhone,
                            message: text
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Mensagem enviada com sucesso!', data);

                                // Adiciona visualmente ao chat
                                const now = new Date();
                                this.messages.push({
                                    tempId: Date.now(),
                                    direction: 'saida',
                                    content: text,
                                    time: now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0')
                                });
                                this.scrollToBottom();

                            } else {
                                console.error('Erro ao enviar:', data);
                                alert('Erro ao enviar mensagem: ' + JSON.stringify(data.details || data.error));
                            }
                        })
                        .catch(error => {
                            console.error('Erro de rede:', error);
                            alert('Erro de conexão ao enviar mensagem.');
                        });

                    this.newMessage = ''; // Limpa o campo imediatamente
                }
            }))
        })
    </script>
</x-app-layout>