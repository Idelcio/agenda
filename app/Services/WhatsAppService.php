<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use App\Models\User;
use App\Models\Appointment;
use App\Models\ChatbotMessage;
use App\Support\WhatsAppMessageFingerprint;

class WhatsAppService
{
    private array $config;
    private string $baseUrl;

    public function __construct()
    {
        $this->config = config('services.api_brasil', []);
        $this->baseUrl = rtrim($this->config['url'] ?? 'https://gateway.apibrasil.io/api/v2/whatsapp', '/');
    }

    /**
     * Inicia uma sessão (gera QRCode quando necessário).
     */
    public function startSession(?string $deviceName = null, ?string $number = null, ?int $autoCloseMs = null): array
    {
        $payload = array_filter([
            'powered_by' => config('app.name', 'Agenda Digital'),
            'device_name' => $deviceName,
            'number' => $number ? $this->normalizeNumber($number) : null,
            'auto_close' => $autoCloseMs,
            'profile_id' => $this->config['profile_id'] ?? null,
        ], fn($value) => $value !== null && $value !== '');

        return $this->post('start', $payload);
    }

    /**
     * Recupera o QR Code do dispositivo.
     */
    public function getQrCode(string $devicePassword): array
    {
        $payload = [
            'device_password' => $devicePassword,
        ];

        return $this->post('qrcode', $payload);
    }

    /**
     * Envia uma mensagem de texto simples.
     */
    public function sendMessage(string $number, string $text, ?int $typingMs = null): array
    {
        return $this->sendText($number, $text, $typingMs);
    }

    public function sendText(string $number, string $text, ?int $typingMs = null): array
    {
        $payload = array_filter([
            'number' => $this->normalizeNumber($number),
            'text' => $text,
            'time_typing' => $typingMs,
        ], fn($value) => $value !== null && $value !== '');

        return $this->post('sendText', $payload);
    }

    /**
     * Envia arquivos/imagens codificados em base64.
     */
    public function sendMediaFromBase64(string $number, string $base64, ?string $caption = null, ?int $typingMs = null): array
    {
        $payload = array_filter([
            'number' => $this->normalizeNumber($number),
            'path' => $base64,
            'caption' => $caption,
            'time_typing' => $typingMs,
        ], fn($value) => $value !== null && $value !== '');

        return $this->post('sendFile64', $payload);
    }

    /**
     * Envia uma lista de opções (botões) para o usuário.
     */
    public function sendList(string $number, string $buttonText, string $description, array $sections, array $options = []): array
    {
        $payload = [
            'number' => $this->normalizeNumber($number),
            'buttonText' => $buttonText,
            'description' => $description,
            'sections' => $sections,
        ];

        if (isset($options['time_typing'])) {
            $payload['time_typing'] = $options['time_typing'];
        }

        return $this->post('sendList', $payload);
    }

    /**
     * Envia um conjunto simples de botões interativos.
     */
    public function sendButtons(string $number, string $text, array $buttons, array $options = []): array
    {
        if (empty($buttons)) {
            throw new RuntimeException('Defina ao menos um botão para a mensagem interativa.');
        }

        $payload = [
            'number' => $this->normalizeNumber($number),
            'text' => $text,
            'options' => array_merge([
                'useTemplateButtons' => true,
                'buttons' => array_map(function (array $button) {
                    $label = trim($button['text'] ?? '');
                    if ($label === '') {
                        throw new RuntimeException('Texto do botão interativo obrigatório.');
                    }
                    return $button;
                }, $buttons),
            ], $options),
        ];

        return $this->post('sendButtons', $payload);
    }

    /**
     * Responde a uma mensagem específica.
     */
    public function reply(string $number, string $messageId, string $text, ?int $typingMs = null): array
    {
        $payload = array_filter([
            'number' => $this->normalizeNumber($number),
            'messageid' => $messageId,
            'text' => $text,
            'time_typing' => $typingMs,
        ], fn($value) => $value !== null && $value !== '');

        return $this->post('reply', $payload);
    }

    /**
     * 🔹 DESABILITADO - O webhook já processa mensagens em tempo real
     *
     * Esta função causava duplicatas porque processava as mesmas mensagens que o webhook.
     * Como o webhook está funcionando corretamente, não precisamos mais buscar mensagens manualmente.
     */
    public function fetchNewMessagesAndProcess(): void
    {
        Log::info('⚠️ fetchNewMessagesAndProcess() está DESABILITADO - usando webhook em tempo real');

        // NÃO FAZ NADA - webhook processa tudo
        return;
    }

    /**
     * 🔹 Processa cada mensagem recebida
     */
    public function processIncomingMessage(string $from, string $body, array $payload): void
    {
        // 🔹 IGNORA MENSAGENS ENVIADAS PELO PRÓPRIO SISTEMA (fromMe = true)
        $isFromMe = data_get($payload, 'fromMe', false)
                 ?? data_get($payload, 'data.fromMe', false)
                 ?? data_get($payload, 'data.data.id.fromMe', false);

        if ($isFromMe) {
            Log::info('🚫 Mensagem ignorada em processIncomingMessage (enviada pelo próprio sistema)', [
                'from' => $from,
            ]);
            return;
        }

        // 🔹 Extrai ID único da mensagem (API Brasil envia isso em vários níveis)
        $externalId = WhatsAppMessageFingerprint::forPayload($payload, $from, $body);

        // 🔹 Evita processar a mesma mensagem duas vezes
        if (ChatbotMessage::where('external_id', $externalId)->exists()) {
            Log::info('⚠️ Mensagem duplicada ignorada (já processada)', [
                'from' => $from,
                'id' => $externalId,
            ]);
            return;
        }

        // 🔹 Busca o usuário (empresa ou cliente vinculado)
        $user = User::where('whatsapp_number', $from)->first();

        if (!$user) {
            $empresa = User::whereHas('clientes', function ($query) use ($from) {
                $query->where('whatsapp_number', $from);
            })->first();

            if ($empresa) {
                $user = $empresa->clientes()->where('whatsapp_number', $from)->first();
            }
        }

        // 🔹 Registra a mensagem recebida no histórico
        ChatbotMessage::create([
            'user_id' => $user?->id,
            'whatsapp_numero' => $from,
            'direcao' => 'entrada',
            'conteudo' => $body,
            'payload' => $payload,
            'external_id' => $externalId, // 🔸 novo campo
        ]);

        if (!$user) {
            Log::warning('🚫 Mensagem recebida de número não registrado', ['from' => $from]);
            return;
        }

        // 🔹 Normaliza corpo da mensagem
        $normalized = strtoupper(trim($body));
        $normalized = preg_replace('/[\s\n\r\t\x{200B}-\x{200D}\x{FEFF}]+/u', '', $normalized);
        $normalized = str_replace(['️⃣', '⃣', '✖️', '✔️', '1️⃣', '2️⃣'], '', $normalized);
        $normalized = preg_replace('/[^\p{L}\p{N}]/u', '', $normalized);

        if (str_starts_with($normalized, '1')) {
            $normalized = '1';
        } elseif (str_starts_with($normalized, '2')) {
            $normalized = '2';
        }

        // 🔹 Busca SOMENTE compromisso PENDENTE vinculado ao usuário (não processa confirmados/cancelados)
        $appointment = Appointment::query()
            ->where(function ($query) use ($user, $from) {
                if ($user->tipo === 'cliente') {
                    $query->where('destinatario_user_id', $user->id);
                } else {
                    $query->where('user_id', $user->id)
                        ->orWhere('whatsapp_numero', $from);
                }
            })
            ->where('status', 'pendente') // 🔹 SÓ COMPROMISSOS PENDENTES (não confirmados nem cancelados)
            ->where('status_lembrete', 'enviado') // 🔹 SÓ LEMBRETES JÁ ENVIADOS
            ->latest('lembrete_enviado_em')
            ->first();

        if (! $appointment) {
            Log::info('⚠️ Nenhum compromisso pendente encontrado para este usuário.', [
                'whatsapp' => $from,
                'user_id' => $user->id ?? null,
            ]);
            return;
        }

        Log::info('📩 Mensagem recebida no processIncomingMessage', [
            'from' => $from,
            'body' => $body,
        ]);

        // 🔹 Interpreta comandos conhecidos
        $isConfirm = in_array($normalized, ['1', 'UM', 'CONFIRMAR', 'SIM', 'OK', 'CONCLUIR']);
        $isCancel  = in_array($normalized, ['2', 'DOIS', 'CANCELAR', 'NÃO', 'NAO', 'CANCEL']);

        Log::info('🔍 Verificando comando', [
            'normalized' => $normalized,
            'isConfirm' => $isConfirm,
            'isCancel' => $isCancel,
            'appointment_id' => $appointment->id,
            'status_atual' => $appointment->status,
        ]);

        if ($isConfirm) {
            $appointment->update([
                'status' => 'confirmado',
                'status_lembrete' => 'respondido', // 🔹 Marca como respondido para não processar novamente
            ]);

            Log::info('✅ Agendamento confirmado via WhatsApp para ' . $from);
        } elseif ($isCancel) {
            $appointment->update([
                'status' => 'cancelado',
                'status_lembrete' => 'respondido', // 🔹 Marca como respondido para não processar novamente
            ]);

            Log::info('❌ Agendamento cancelado via WhatsApp para ' . $from);
        } else {
            Log::info('💬 Mensagem ignorada (não é resposta válida de confirmação): ' . $body);
        }
    }



    /**
     * Recupera as mensagens de um chat específico.
     */
    public function fetchChatMessages(string $number, ?string $direction = null, ?int $count = null): array
    {
        $payload = array_filter([
            'number' => $this->normalizeNumber($number),
            'direction' => $direction,
            'count' => $count,
        ], fn($value) => $value !== null && $value !== '');

        $response = $this->post('getMessagesChat', $payload);
        return $response['data'] ?? $response;
    }

    /**
     * 🔧 Execução padrão de requisição à API Brasil
     */
    private function post(string $endpoint, array $payload = []): array
    {
        $this->ensureCredentials();
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $headers = array_filter([
            'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
            'DeviceToken' => $this->config['device_token'] ?? '',
            'ProfileId' => $this->config['profile_id'] ?? '',
            'DeviceId' => $this->config['device_id'] ?? '',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        $http = Http::withHeaders($headers)
            ->timeout(config('services.api_brasil.timeout', 25))
            ->connectTimeout(config('services.api_brasil.connect_timeout', 10))
            ->retry(
                config('services.api_brasil.retry_times', 1),
                config('services.api_brasil.retry_sleep', 1000),
                throw: false // Don't throw exception automatically, we'll handle errors manually
            );

        try {
            $response = $http->post($url, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            throw new RuntimeException('API Brasil não respondeu dentro do tempo limite.', 0, $exception);
        }

        if ($response->failed()) {
            $body = $response->json();
            $statusCode = $response->status();
            $error = $body['message'] ?? $body['error'] ?? $response->body();

            Log::error('API Brasil retornou erro', [
                'endpoint' => $endpoint,
                'status' => $statusCode,
                'error' => $error,
                'body' => $body,
            ]);

            throw new RuntimeException(
                sprintf('API Brasil retornou erro %d: %s', $statusCode, $error)
            );
        }

        return $response->json() ?? [];
    }

    private function ensureCredentials(): void
    {
        if (empty($this->config['token']) || empty($this->config['device_token'])) {
            throw new RuntimeException('Credenciais da API Brasil/WhatsApp não configuradas.');
        }
    }

    private function normalizeNumber(string $number): string
    {
        // Remove tudo que não for número
        $digits = preg_replace('/\D+/', '', $number);

        // Garante que tenha o código do Brasil no início
        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        // Detecta DDD + número com ou sem o 9
        // Exemplo: 555196244848 → faltando o 9
        // Exemplo: 5551996244848 → já tem o 9
        if (strlen($digits) === 12) {
            // Inserir o 9 após o DDD (depois de 4 dígitos)
            $digits = substr($digits, 0, 4) . '9' . substr($digits, 4);
        }

        if ($digits === '' || strlen($digits) < 12) {
            throw new RuntimeException('Número de WhatsApp inválido: ' . $number);
        }

        return $digits;
    }

    /**
     * Cria um novo device/sessão na API Brasil (usando token mestre)
     * Faz requisição direta sem validar device_token (pois ainda não existe)
     */
    public function createDevice(string $deviceName): array
    {
        try {
            Log::info('Criando device na API Brasil', [
                'device_name' => $deviceName,
            ]);

            $url = $this->baseUrl . '/start';

            $headers = array_filter([
                'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
                // NÃO envia DeviceToken aqui, pois estamos CRIANDO um novo
                'ProfileId' => $this->config['profile_id'] ?? '',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

            $payload = array_filter([
                'powered_by' => config('app.name', 'Agenda Digital'),
                'device_name' => $deviceName,
            ]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($url, $payload);

            $data = $response->json();

            Log::info('Resposta da API Brasil ao criar device', [
                'status' => $response->status(),
                'data' => $data,
            ]);

            if ($response->failed()) {
                $error = $data['message'] ?? $data['error'] ?? $response->body();
                throw new RuntimeException('Erro ao criar device: ' . $error);
            }

            // A API Brasil retorna o device_token na resposta
            $deviceToken = $data['device_token']
                        ?? $data['response']['device_token']
                        ?? $data['device']['device_token']
                        ?? null;

            $deviceId = $data['device_id']
                     ?? $data['response']['device_id']
                     ?? $data['device']['device_id']
                     ?? null;

            if (!$deviceToken) {
                throw new RuntimeException('API Brasil não retornou device_token. Resposta: ' . json_encode($data));
            }

            return [
                'device_token' => $deviceToken,
                'device_id' => $deviceId,
                'full_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Exceção ao criar device: ' . $e->getMessage(), [
                'exception' => get_class($e),
            ]);
            throw $e;
        }
    }

    /**
     * Obtém o QR Code de um device específico
     */
    public function getDeviceQrCode(string $deviceToken): ?string
    {
        $url = rtrim($this->config['url'] ?? 'https://gateway.apibrasil.io/api/v2/whatsapp', '/') . '/qrcode';

        $headers = [
            'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
            'DeviceToken' => $deviceToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        try {
            Log::info('Buscando QR Code', ['device_token' => substr($deviceToken, 0, 10) . '...']);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($url, []); // API Brasil usa POST para qrcode

            if ($response->failed()) {
                Log::error('Erro ao obter QR Code', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            Log::info('Resposta QR Code recebida', [
                'has_qrcode' => isset($data['qrcode']) || isset($data['data']['qrcode']) || isset($data['response']['qrcode']),
                'keys' => array_keys($data),
            ]);

            // A API Brasil pode retornar o QR Code em diferentes formatos
            $qrcode = $data['qrcode']
                   ?? $data['data']['qrcode']
                   ?? $data['response']['qrcode']
                   ?? $data['base64']
                   ?? null;

            return $qrcode;

        } catch (\Exception $e) {
            Log::error('Exceção ao obter QR Code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica o status de conexão de um device específico
     */
    public function checkDeviceStatus(string $deviceToken): array
    {
        $url = rtrim($this->config['url'] ?? 'https://gateway.apibrasil.io/api/v2/whatsapp', '/') . '/status';

        $headers = [
            'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
            'DeviceToken' => $deviceToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($url, []); // API Brasil geralmente usa POST

            $data = $response->json() ?? [];

            // 🔹 Mesmo com erro 401, a API Brasil retorna o status real do device
            if ($response->failed()) {
                Log::warning('API retornou erro ao verificar status, mas vamos checar a resposta', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // ✅ Extrai o status do device mesmo no erro
                $deviceStatus = $data['device']['status'] ?? null;

                // Status válidos de conexão: "inChat", "qrcode", "open", "connected"
                $statusesAtivos = ['inChat', 'qrcode', 'open', 'connected', 'isLogged'];

                if ($deviceStatus && in_array($deviceStatus, $statusesAtivos)) {
                    Log::info('✅ Device está conectado (extraído da resposta de erro)', [
                        'device_status' => $deviceStatus,
                    ]);

                    return [
                        'connected' => true,
                        'status' => $deviceStatus,
                        'full_response' => $data,
                    ];
                }

                return ['connected' => false, 'full_response' => $data];
            }

            Log::debug('Status do device verificado', [
                'data' => $data,
            ]);

            // Verifica se está conectado baseado na resposta (quando não há erro)
            $connected = ($data['connected'] ?? false)
                      || ($data['status'] ?? '') === 'connected'
                      || ($data['response']['connected'] ?? false)
                      || ($data['state'] ?? '') === 'open'
                      || ($data['device']['status'] ?? '') === 'inChat';

            return [
                'connected' => $connected,
                'full_response' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('Exceção ao verificar status: ' . $e->getMessage());
            return ['connected' => false];
        }
    }

    /**
     * Configura o serviço para usar credenciais de um device específico
     * (usado para enviar mensagens com credenciais da empresa)
     */
    public function setDeviceCredentials(?string $deviceToken, ?string $deviceId = null): void
    {
        if ($deviceToken) {
            $this->config['device_token'] = $deviceToken;
        }
        if ($deviceId) {
            $this->config['device_id'] = $deviceId;
        }
    }

    /**
     * Configura credenciais baseado em um usuário (empresa)
     * Busca as credenciais do device da empresa no banco
     */
    public function useUserCredentials(User $user): void
    {
        // Se for cliente, busca a empresa pai
        if ($user->tipo === 'cliente' && $user->user_id) {
            $user = User::find($user->user_id);
        }

        // Se for empresa e tem credenciais configuradas, usa elas
        if ($user && $user->tipo === 'empresa' && $user->apibrasil_device_token) {
            $this->setDeviceCredentials(
                $user->apibrasil_device_token,
                $user->apibrasil_device_id
            );

            Log::info('Usando credenciais da empresa', [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
        } else {
            // Fallback: usa credenciais do .env (padrão)
            Log::warning('Usando credenciais padrão do .env', [
                'user_id' => $user->id ?? null,
                'tipo' => $user->tipo ?? null,
            ]);
        }
    }
}
