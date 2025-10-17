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
                            Siga os passos abaixo:
                        </p>
                    </div>

                    <!-- Etapa -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4" id="step-1">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white font-bold mr-3">
                                ✓
                            </div>
                            <p class="text-gray-700">Inserir credenciais do dispositivo</p>
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
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Instruções:</strong> Acesse o painel da API Brasil, crie um novo dispositivo, escaneie o QR Code pelo notebook e depois copie as credenciais abaixo.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form id="form-credentials" class="space-y-4">
                                <div>
                                    <label for="device_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Senha Dispositivo (device_name)
                                    </label>
                                    <input
                                        type="text"
                                        id="device_name"
                                        name="device_name"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: v9demxaa">
                                </div>

                                <div>
                                    <label for="device_token" class="block text-sm font-medium text-gray-700 mb-1">
                                        DeviceToken
                                    </label>
                                    <input
                                        type="text"
                                        id="device_token"
                                        name="device_token"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 4db434ed-222d-4a5f-b4e6-63d73e45aa50">
                                </div>

                                <div class="pt-4">
                                    <button
                                        type="submit"
                                        id="btn-save-credentials"
                                        class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                        Salvar e Continuar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Loading -->
                        <div id="loading-step" class="hidden text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                            <p class="mt-4 text-gray-600">Salvando credenciais...</p>
                        </div>

                        <!-- Verificando Conexão -->
                        <div id="checking-step" class="hidden text-center">
                            <div class="mb-4">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">
                                Verificando Conexão
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                Aguarde enquanto verificamos se o WhatsApp está conectado...
                            </p>
                            <p class="text-xs text-gray-500">
                                Certifique-se de que escaneou o QR Code no painel da API Brasil antes de salvar as credenciais.
                            </p>
                        </div>

                        <!-- Conectado -->
                        <div id="connected-step" class="hidden text-center">
                            <div class="mb-4">
                                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-green-600 mb-2">
                                WhatsApp Conectado com Sucesso!
                            </h4>
                            <p class="text-sm text-gray-600 mb-6">
                                Seu número está conectado e pronto para uso.
                            </p>
                            <button
                                id="btn-complete-setup"
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
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700" id="error-message">
                                            Ocorreu um erro. Tente novamente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button
                                id="btn-retry"
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

        const initialStep = document.getElementById('initial-step');
        const loadingStep = document.getElementById('loading-step');
        const errorStep = document.getElementById('error-step');

        function showStep(step) {
            initialStep.classList.add('hidden');
            loadingStep.classList.add('hidden');
            errorStep.classList.add('hidden');

            step.classList.remove('hidden');
        }

        function showError(message) {
            document.getElementById('error-message').textContent = message;
            showStep(errorStep);
        }

        formCredentials.addEventListener('submit', async (e) => {
            e.preventDefault();

            showStep(loadingStep);

            const deviceName = document.getElementById('device_name').value;
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
                        device_token: deviceToken
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Credenciais salvas, redireciona direto para agenda
                    window.location.href = data.redirect;
                } else {
                    showError(data.message || 'Erro ao salvar credenciais');
                }
            } catch (error) {
                showError('Erro de conexão. Verifique sua internet.');
            }
        });

        btnRetry.addEventListener('click', () => {
            showStep(initialStep);
        });
    </script>
</x-app-layout>
