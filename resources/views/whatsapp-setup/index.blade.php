<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configuração do WhatsApp
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            Bem-vindo(a)! Configure seu WhatsApp
                        </h3>
                        <p class="text-sm text-gray-600">
                            Para usar o sistema de agenda, você precisa conectar seu número do WhatsApp.
                            Insira as credenciais do dispositivo e gere o QR Code direto aqui.
                        </p>
                    </div>

                    <!-- Etapa -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4" id="step-1">
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white font-bold mr-3">
                                1
                            </div>
                            <p class="text-gray-700">Inserir credenciais e gerar QR Code</p>
                        </div>
                    </div>

                    <!-- Área de ação -->
                    <div class="border-t pt-6">
                        <!-- Formulário de credenciais -->
                        <div id="initial-step">
                            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Instruções:</strong> Acesse o painel da API Brasil, crie um novo
                                            dispositivo, copie as credenciais abaixo e clique em <strong>"Gerar QR
                                                Code"</strong>.
                                            O QR Code aparecerá aqui mesmo — não precisa escanear lá.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form id="form-credentials" class="space-y-4">
                                <div>
                                    <label for="device_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Senha do Dispositivo (device_name)
                                    </label>
                                    <input type="text" id="device_name" name="device_name" required
                                        value="{{ Auth::user()->apibrasil_device_name }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: v9demxaa">
                                </div>

                                <div>
                                    <label for="device_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Device ID
                                    </label>
                                    <input type="text" id="device_id" name="device_id" required
                                        value="{{ Auth::user()->apibrasil_device_id }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 4db434ed-222d-4a5f-b4e6-63d73e45aa50">
                                </div>

                                <div>
                                    <label for="device_token" class="block text-sm font-medium text-gray-700 mb-1">
                                        DeviceToken
                                    </label>
                                    <input type="text" id="device_token" name="device_token"
                                        value="{{ Auth::user()->apibrasil_device_token }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 14edb6cf-1398-41c7-9109-af9b584b40ab">
                                </div>

                                <div class="pt-4 flex gap-3">
                                    <button type="submit" id="btn-save-credentials"
                                        class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                        Salvar Credenciais
                                    </button>
                                    <button type="button" id="btn-generate-qr"
                                        class="flex-1 bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                        📱 Gerar QR Code
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Loading -->
                        <div id="loading-step" class="hidden text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                            <p class="mt-4 text-gray-600" id="loading-message">Salvando credenciais...</p>
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
                                Abra o WhatsApp no celular → <strong>Dispositivos conectados</strong> → <strong>Conectar
                                    dispositivo</strong>
                            </p>
                            <p class="text-xs text-gray-400 mb-4" id="qr-timer">O QR Code expira em 60 segundos</p>
                            <div class="flex gap-3 justify-center">
                                <button id="btn-refresh-qr"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                    🔄 Atualizar QR Code
                                </button>
                                <button id="btn-check-after-qr"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                    ✅ Já escaneei
                                </button>
                            </div>
                        </div>

                        <!-- Verificando Conexão -->
                        <div id="checking-step" class="hidden text-center">
                            <div class="mb-4">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto">
                                </div>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">
                                Verificando Conexão
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                Aguarde enquanto verificamos se o WhatsApp está conectado...
                            </p>
                        </div>

                        <!-- Conectado -->
                        <div id="connected-step" class="hidden text-center">
                            <div class="mb-4">
                                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-green-600 mb-2">
                                WhatsApp Conectado com Sucesso!
                            </h4>
                            <p class="text-sm text-gray-600 mb-6">
                                Seu número está conectado e pronto para uso.
                            </p>
                            <button id="btn-complete-setup"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                Ir para Agenda
                            </button>
                        </div>

                        <!-- Erro -->
                        <div id="error-step" class="hidden">
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700" id="error-message">
                                            Ocorreu um erro. Tente novamente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button id="btn-retry"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Tentar Novamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const formCredentials = document.getElementById('form-credentials');
        const btnRetry = document.getElementById('btn-retry');
        const btnGenerateQr = document.getElementById('btn-generate-qr');
        const btnRefreshQr = document.getElementById('btn-refresh-qr');
        const btnCheckAfterQr = document.getElementById('btn-check-after-qr');

        const initialStep = document.getElementById('initial-step');
        const loadingStep = document.getElementById('loading-step');
        const qrcodeStep = document.getElementById('qrcode-step');
        const checkingStep = document.getElementById('checking-step');
        const connectedStep = document.getElementById('connected-step');
        const errorStep = document.getElementById('error-step');

        function showStep(step) {
            [initialStep, loadingStep, qrcodeStep, checkingStep, connectedStep, errorStep]
                .forEach(s => s.classList.add('hidden'));
            step.classList.remove('hidden');
        }

        function showError(message) {
            document.getElementById('error-message').textContent = message;
            showStep(errorStep);
        }

        // Sincronização Device ID → Device Token
        document.getElementById('device_id').addEventListener('input', (e) => {
            const idValue = e.target.value.trim();
            const tokenInput = document.getElementById('device_token');
            if (!tokenInput.value || tokenInput.value.length < 5) {
                tokenInput.value = idValue;
            }
        });

        // Salvar credenciais
        formCredentials.addEventListener('submit', async (e) => {
            e.preventDefault();
            document.getElementById('loading-message').textContent = 'Salvando credenciais...';
            showStep(loadingStep);

            const deviceName = document.getElementById('device_name').value;
            const deviceId = document.getElementById('device_id').value;
            const deviceToken = document.getElementById('device_token').value;

            try {
                const response = await fetch('{{ route('setup-whatsapp.save-credentials') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_name: deviceName,
                        device_id: deviceId,
                        device_token: deviceToken
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    showError(data.message || 'Erro ao salvar credenciais');
                }
            } catch (error) {
                showError('Erro de conexão. Verifique sua internet.');
            }
        });

        // Gerar QR Code
        async function generateQrCode() {
            document.getElementById('loading-message').textContent = 'Gerando QR Code... Aguarde até 15 segundos.';
            showStep(loadingStep);

            const deviceName = document.getElementById('device_name').value;
            const deviceId = document.getElementById('device_id').value;
            const deviceToken = document.getElementById('device_token').value;

            if (!deviceName || !deviceId) {
                showError('Preencha a Senha do Dispositivo e o Device ID.');
                return;
            }

            try {
                // Primeiro salva as credenciais
                await fetch('{{ route('setup-whatsapp.save-credentials') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_name: deviceName,
                        device_id: deviceId,
                        device_token: deviceToken || deviceId
                    })
                });

                // Depois gera o QR Code
                const response = await fetch('{{ route('setup-whatsapp.generate-qrcode') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_name: deviceName,
                        device_id: deviceId,
                        device_token: deviceToken || deviceId
                    })
                });

                const data = await response.json();

                if (data.success && data.qrcode) {
                    const img = document.getElementById('qrcode-image');
                    // Se já for base64 com prefixo ou URL
                    if (data.qrcode.startsWith('data:') || data.qrcode.startsWith('http')) {
                        img.src = data.qrcode;
                    } else {
                        img.src = 'data:image/png;base64,' + data.qrcode;
                    }
                    showStep(qrcodeStep);
                } else {
                    showError(data.message || 'Não foi possível gerar o QR Code. Tente novamente.');
                }
            } catch (error) {
                console.error('Erro:', error);
                showError('Erro de conexão ao gerar QR Code.');
            }
        }

        btnGenerateQr.addEventListener('click', generateQrCode);
        btnRefreshQr.addEventListener('click', generateQrCode);

        // Verificar conexão após escanear QR Code
        btnCheckAfterQr.addEventListener('click', async () => {
            showStep(checkingStep);

            try {
                const response = await fetch('{{ route('setup-whatsapp.check-connection') }}');
                const data = await response.json();

                if (data.success && data.connected) {
                    showStep(connectedStep);
                } else {
                    showError('WhatsApp ainda não está conectado. Escaneie o QR Code e tente novamente.');
                }
            } catch (error) {
                showError('Erro ao verificar conexão.');
            }
        });

        // Completar setup
        document.getElementById('btn-complete-setup')?.addEventListener('click', async () => {
            try {
                const response = await fetch('{{ route('setup-whatsapp.complete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = '{{ route('agenda.index') }}';
                }
            } catch (error) {
                window.location.href = '{{ route('agenda.index') }}';
            }
        });

        // Tentar novamente
        btnRetry.addEventListener('click', () => {
            showStep(initialStep);
        });
    </script>

</x-app-layout>