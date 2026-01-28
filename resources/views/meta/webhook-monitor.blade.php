<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitor de Webhooks Meta') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">

            {{-- Header --}}
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">📊 Monitor de Webhooks Meta</h1>
                <p class="text-gray-600">Acompanhe em tempo real os eventos do WhatsApp</p>
            </div>

            {{-- Status Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-600 font-medium">Webhook URL</p>
                            <p class="text-xs text-blue-800 mt-1 font-mono break-all">{{ url('/webhooks/meta') }}</p>
                        </div>
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 font-medium">Token de Verificação</p>
                            <p class="text-xs text-green-800 mt-1 font-mono">{{ env('META_WEBHOOK_VERIFY_TOKEN') }}</p>
                        </div>
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-purple-600 font-medium">Status</p>
                            <p class="text-xs text-purple-800 mt-1">Aguardando eventos...</p>
                        </div>
                        <div class="relative flex h-3 w-3">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Configuração Necessária</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p class="mb-2">Para receber eventos da Meta, você precisa:</p>
                            <ol class="list-decimal list-inside space-y-1 ml-2">
                                <li>Expor o localhost usando <strong>ngrok</strong> ou similar</li>
                                <li>Configurar a URL do webhook no painel da Meta</li>
                                <li>Inscrever nos eventos (messages, message_status)</li>
                            </ol>
                            <a href="{{ asset('WEBHOOK_SETUP.md') }}" target="_blank"
                                class="mt-2 inline-flex items-center text-yellow-800 hover:text-yellow-900 font-medium">
                                📖 Ver guia completo de configuração
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logs em Tempo Real --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Últimos Eventos
                    </h2>
                    <button onclick="refreshLogs()" class="text-white hover:text-gray-300 text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Atualizar
                    </button>
                </div>

                <div class="p-6">
                    <div id="logContainer"
                        class="bg-gray-900 rounded-lg p-4 font-mono text-sm text-green-400 h-96 overflow-y-auto">
                        <div class="text-gray-500 text-center py-8">
                            Aguardando eventos do webhook...
                            <br>
                            <span class="text-xs">Os logs aparecerão aqui em tempo real</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                        <span>Auto-refresh a cada 5 segundos</span>
                        <span id="lastUpdate">Nunca atualizado</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('setup-meta.index') }}"
                    class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Enviar Mensagem de Teste
                </a>
                <a href="{{ route('meta.chat.index') }}"
                    class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Abrir Chat
                </a>
            </div>

        </div>
    </div>

    <script>
        let refreshInterval;

        function refreshLogs() {
            fetch('/api/webhook-logs')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('logContainer');
                    if (data.logs && data.logs.length > 0) {
                        container.innerHTML = data.logs.map(log => {
                            const time = new Date(log.timestamp).toLocaleTimeString('pt-BR');
                            const typeColor = log.type === 'message' ? 'text-blue-400' :
                                log.type === 'status' ? 'text-green-400' : 'text-yellow-400';
                            return `<div class="mb-2 pb-2 border-b border-gray-800">
                                <span class="text-gray-500">[${time}]</span>
                                <span class="${typeColor} font-bold">${log.type.toUpperCase()}</span>
                                <span class="text-gray-300">: ${log.message}</span>
                            </div>`;
                        }).join('');

                        // Auto-scroll to bottom
                        container.scrollTop = container.scrollHeight;
                    }

                    document.getElementById('lastUpdate').textContent = 'Atualizado: ' + new Date().toLocaleTimeString('pt-BR');
                })
                .catch(error => {
                    console.error('Erro ao buscar logs:', error);
                });
        }

        // Auto-refresh a cada 5 segundos
        refreshInterval = setInterval(refreshLogs, 5000);

        // Refresh inicial
        refreshLogs();

        // Limpar interval ao sair da página
        window.addEventListener('beforeunload', () => {
            clearInterval(refreshInterval);
        });
    </script>
    </div>
</x-app-layout>