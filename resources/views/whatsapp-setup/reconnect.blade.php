<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reconectar WhatsApp
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Status atual -->
                    <div class="mb-6" id="status-bar">
                        <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div id="status-indicator" class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <span id="status-text" class="text-sm font-medium text-gray-700">Verificando
                                    conexão...</span>
                            </div>
                            <span class="text-xs text-gray-400">Dispositivo:
                                {{ $user->apibrasil_device_name ?? '—' }}</span>
                        </div>
                    </div>

                    <!-- Botão gerar QR Code -->
                    <div id="action-step" class="text-center">
                        <div class="mb-6">
                            <svg class="mx-auto h-16 w-16 text-green-500 opacity-30" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                <path
                                    d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Reconectar WhatsApp</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Clique no botão para gerar um novo QR Code e reconectar seu WhatsApp.<br>
                            Suas credenciais já estão salvas.
                        </p>
                        <button id="btn-generate" onclick="generateQrCode()"
                            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                            <span class="text-xl">📱</span> Gerar QR Code
                        </button>
                    </div>

                    <!-- Loading -->
                    <div id="loading-step" class="hidden text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500 mx-auto"></div>
                        <p class="mt-4 text-gray-600">Gerando QR Code... Aguarde até 20 segundos.</p>
                    </div>

                    <!-- QR Code -->
                    <div id="qrcode-step" class="hidden text-center">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">
                            📱 Escaneie o QR Code com seu WhatsApp
                        </h4>
                        <div class="mb-4 inline-block p-4 bg-white border-2 border-gray-200 rounded-xl shadow-lg">
                            <img id="qrcode-image" src="" alt="QR Code" class="mx-auto"
                                style="max-width: 300px; max-height: 300px;">
                        </div>
                        <p class="text-sm text-gray-600 mb-2">
                            Abra o WhatsApp → <strong>Dispositivos conectados</strong> → <strong>Conectar
                                dispositivo</strong>
                        </p>
                        <p class="text-xs text-gray-400 mb-4">O QR Code expira em ~60 segundos</p>
                        <div class="flex gap-3 justify-center flex-wrap">
                            <button onclick="generateQrCode()"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                🔄 Atualizar QR Code
                            </button>
                            <button onclick="checkConnection()"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                ✅ Já escaneei
                            </button>
                        </div>
                    </div>

                    <!-- Conectado -->
                    <div id="connected-step" class="hidden text-center py-8">
                        <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h4 class="text-lg font-medium text-green-600 mb-2">WhatsApp Conectado!</h4>
                        <p class="text-sm text-gray-600 mb-6">Seu número está conectado e funcionando.</p>
                        <a href="{{ route('agenda.index') }}"
                            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            Ir para Agenda
                        </a>
                    </div>

                    <!-- Erro -->
                    <div id="error-step" class="hidden">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                            <p class="text-sm text-red-700" id="error-message">Ocorreu um erro.</p>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="showStep('action-step')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Tentar Novamente
                            </button>
                            <a href="{{ route('setup-whatsapp.index') }}"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Ir para Setup Completo
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const steps = ['action-step', 'loading-step', 'qrcode-step', 'connected-step', 'error-step'];

        function showStep(id) {
            steps.forEach(s => document.getElementById(s).classList.add('hidden'));
            document.getElementById(id).classList.remove('hidden');
        }

        function showError(msg) {
            document.getElementById('error-message').textContent = msg;
            showStep('error-step');
        }

        async function generateQrCode() {
            showStep('loading-step');

            try {
                const response = await fetch('{{ route("setup-whatsapp.generate-qrcode") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();

                if (data.success && data.qrcode) {
                    const img = document.getElementById('qrcode-image');
                    if (data.qrcode.startsWith('data:') || data.qrcode.startsWith('http')) {
                        img.src = data.qrcode;
                    } else {
                        img.src = 'data:image/png;base64,' + data.qrcode;
                    }
                    showStep('qrcode-step');
                } else {
                    showError(data.message || 'QR Code não disponível. Tente novamente.');
                }
            } catch (error) {
                console.error('Erro:', error);
                showError('Erro de conexão ao gerar QR Code.');
            }
        }

        async function checkConnection() {
            showStep('loading-step');

            try {
                const response = await fetch('{{ route("setup-whatsapp.check-connection") }}');
                const data = await response.json();

                if (data.success && data.connected) {
                    setStatus('connected');
                    showStep('connected-step');
                } else {
                    showError('WhatsApp ainda não conectado. Escaneie o QR Code e tente novamente.');
                }
            } catch (error) {
                showError('Erro ao verificar conexão.');
            }
        }

        function setStatus(status) {
            const indicator = document.getElementById('status-indicator');
            const text = document.getElementById('status-text');

            if (status === 'connected') {
                indicator.className = 'w-3 h-3 rounded-full bg-green-500';
                text.textContent = 'Conectado';
                text.className = 'text-sm font-medium text-green-700';
            } else if (status === 'disconnected') {
                indicator.className = 'w-3 h-3 rounded-full bg-red-500';
                text.textContent = 'Desconectado';
                text.className = 'text-sm font-medium text-red-700';
            } else {
                indicator.className = 'w-3 h-3 rounded-full bg-yellow-400';
                text.textContent = 'Verificando...';
                text.className = 'text-sm font-medium text-gray-700';
            }
        }

        // Verifica status ao carregar a página
        (async () => {
            try {
                const response = await fetch('{{ route("setup-whatsapp.check-connection") }}');
                const data = await response.json();
                setStatus(data.success && data.connected ? 'connected' : 'disconnected');
            } catch {
                setStatus('disconnected');
            }
        })();
    </script>

</x-app-layout>