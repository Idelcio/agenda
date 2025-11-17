<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use App\Models\User;
use App\Models\ChatbotMessage;
use App\Models\Appointment;



class WhatsAppService
{

    private array $config;
    private string $baseUrl;

    public function __construct()
    {
        // Carrega as configurações do config/services.php
        $this->config = config('services.api_brasil', []);

        // Garante que a URL não tenha "/" duplicado
        $this->baseUrl = rtrim($this->config['url'] ?? 'https://gateway.apibrasil.io/api/v2/whatsapp', '/');
    }

    /**
     * Inicia uma sessão (geralmente usada para obter QR Code).
     * Parâmetros opcionais permitem criar device com nome, número e autoclose.
     */
    public function startSession(?string $deviceName = null, ?string $number = null, ?int $autoCloseMs = null): array
    {
        $payload = array_filter([
            'powered_by'   => config('app.name', 'Agendoo'),
            'device_name'  => $deviceName,
            'number'       => $number ? $this->normalizeNumber($number) : null,
            'auto_close'   => $autoCloseMs,
            'profile_id'   => $this->config['profile_id'] ?? null,
        ]);

        return $this->post('start', $payload);
    }

    /**
     * Recupera QR Code de login de um device específico.
     */
    public function getQrCode(string $devicePassword): array
    {
        return $this->post('qrcode', [
            'device_password' => $devicePassword,
        ]);
    }

    /**
     * Envio simples de texto.
     * Usa sendText por baixo dos panos.
     */
    public function sendMessage(string $number, string $text, ?int $typingMs = null): array
    {
        $this->assertDeviceConfigured();
        return $this->sendText($number, $text, $typingMs);
    }

    /**
     * Envia texto, com opção de tempo de digitação artificial.
     */
    public function sendText(string $number, string $text, ?int $typingMs = null): array
    {
        $this->assertDeviceConfigured();

        $payload = array_filter([
            'number'       => $this->normalizeNumber($number),
            'text'         => $text,
            'time_typing'  => $typingMs,
        ]);

        return $this->post('sendText', $payload);
    }

    /**
     * Envia mídia/arquivo codificado em base64.
     */
    public function sendMediaFromBase64(string $number, string $base64, ?string $caption = null, ?int $typingMs = null): array
    {
        $this->assertDeviceConfigured();

        $payload = array_filter([
            'number'       => $this->normalizeNumber($number),
            'path'         => $base64,
            'caption'      => $caption,
            'time_typing'  => $typingMs,
        ]);

        return $this->post('sendFile64', $payload);
    }

    /**
     * Envia lista interativa (com seções e opções).
     */
    public function sendList(string $number, string $buttonText, string $description, array $sections, array $options = []): array
    {
        $payload = [
            'number'      => $this->normalizeNumber($number),
            'buttonText'  => $buttonText,
            'description' => $description,
            'sections'    => $sections,
        ];

        if (isset($options['time_typing'])) {
            $payload['time_typing'] = $options['time_typing'];
        }

        return $this->post('sendList', $payload);
    }

    /**
     * Envia botões simples (tipo "Confirmar" / "Cancelar").
     */
    public function sendButtons(string $number, string $text, array $buttons, array $options = []): array
    {
        if (empty($buttons)) {
            throw new RuntimeException('Defina ao menos um botão para mensagem interativa.');
        }

        // Valida botões
        $mappedButtons = array_map(function ($btn) {
            $label = trim($btn['text'] ?? '');
            if ($label === '') {
                throw new RuntimeException('Texto do botão interativo obrigatório.');
            }
            return $btn;
        }, $buttons);

        $payload = [
            'number'  => $this->normalizeNumber($number),
            'text'    => $text,
            'options' => array_merge([
                'useTemplateButtons' => true,
                'buttons'            => $mappedButtons,
            ], $options),
        ];

        return $this->post('sendButtons', $payload);
    }

    /**
     * Responde uma mensagem específica (reply).
     */
    public function reply(string $number, string $messageId, string $text, ?int $typingMs = null): array
    {
        $payload = array_filter([
            'number'     => $this->normalizeNumber($number),
            'messageid'  => $messageId,
            'text'       => $text,
            'time_typing' => $typingMs,
        ]);

        return $this->post('reply', $payload);
    }

    /**
     * Busca novas mensagens e processa comandos 1/2.
     * Parte central do fluxo de agendamentos via WhatsApp.
     */
    public function fetchNewMessagesAndProcess(): void
    {
        $response = $this->post('getAllNewMessages');
        $data = $response['response']['contacts'] ?? [];

        if (empty($data)) {
            Log::info('Nenhuma nova mensagem recebida.');
            return;
        }

        foreach ($data as $msg) {
            $fromRaw = data_get($msg, 'from', '');
            $body    = trim(data_get($msg, 'body', ''));

            // Ignora mensagens de grupo, status e broadcast
            if (
                str_contains($fromRaw, '@g.us') ||
                str_contains($fromRaw, '@broadcast') ||
                str_contains($fromRaw, '@status') ||
                str_contains($fromRaw, '@newsletter')
            ) {
                Log::info('Ignorando mensagem de grupo/broadcast', ['from' => $fromRaw]);
                continue;
            }

            // Extrai somente dígitos
            $from = preg_replace('/\D+/', '', $fromRaw);

            // Proteção contra IDs gigantes (grupos)
            if (strlen($from) > 13 || strlen($from) < 11) {
                Log::info('Ignorando número inválido', ['from' => $fromRaw]);
                continue;
            }

            // Garantir prefixo 55 (Brasil)
            if (!str_starts_with($from, '55')) {
                $from = '55' . $from;
            }

            // Adiciona dígito 9 se necessário
            if (strlen($from) === 12) {
                $from = substr($from, 0, 4) . '9' . substr($from, 4);
            }

            // Só processa se existir corpo da mensagem
            if ($from && $body !== '') {
                $this->processIncomingMessage($from, $body, $msg);
            }

            // Pequena pausa entre processamentos
            usleep(300000);
        }
    }

    /**
     * Processa cada mensagem recebida.
     * Aqui acontece:
     * - Identificação do usuário
     * - Registro no histórico
     * - Interpretação de comandos
     * - Troca de status do agendamento
     */
    public function processIncomingMessage(string $from, string $body, array $payload): void
    {
        // Identifica ID único da mensagem
        $externalId =
            data_get($payload, 'id') ??
            data_get($payload, 'message.id') ??
            data_get($payload, 'data.id') ??
            data_get($payload, 'response.id') ??
            Str::uuid()->toString();

        // Evita duplicidade de processamento
        if (ChatbotMessage::where('external_id', $externalId)->exists()) {
            Log::info('Mensagem duplicada ignorada', [
                'from' => $from,
                'id'   => $externalId,
            ]);
            return;
        }

        // Identifica usuário (cliente → empresa → relacionamento)
        $user = User::where('whatsapp_number', $from)->first();

        if (!$user) {
            $empresa = User::whereHas('clientes', function ($q) use ($from) {
                $q->where('whatsapp_number', $from);
            })->first();

            if ($empresa) {
                $user = $empresa->clientes()->where('whatsapp_number', $from)->first();
            }
        }

        // Registra a entrada no histórico
        ChatbotMessage::create([
            'user_id'        => $user?->id,
            'whatsapp_numero' => $from,
            'direcao'        => 'entrada',
            'conteudo'       => $body,
            'payload'        => $payload,
            'external_id'    => $externalId,
        ]);

        if (!$user) {
            Log::warning('Mensagem recebida de número não registrado', ['from' => $from]);
            return;
        }

        // Normaliza texto (tira acentos, espaços, caracteres invisíveis)
        $normalized = strtoupper(trim($body));
        $normalized = preg_replace('/[\s\n\r\t\x{200B}-\x{200D}\x{FEFF}]+/u', '', $normalized);
        $normalized = preg_replace('/[^\p{L}\p{N}]/u', '', $normalized);

        // Reduz texto para "1" ou "2" quando aplicável
        if (str_starts_with($normalized, '1')) $normalized = '1';
        if (str_starts_with($normalized, '2')) $normalized = '2';

        // Busca compromisso pendente/atual do usuário
        $appointment = Appointment::query()
            ->where(function ($q) use ($user, $from) {
                if ($user->tipo === 'cliente') {
                    $q->where('destinatario_user_id', $user->id);
                } else {
                    $q->where('user_id', $user->id)
                        ->orWhere('whatsapp_numero', $from);
                }
            })
            ->whereIn('status', ['pendente', 'confirmado', 'cancelado'])
            ->latest('inicio')
            ->first();

        if (!$appointment) {
            Log::info('Nenhum compromisso relevante encontrado', [
                'whatsapp' => $from,
                'user_id'  => $user->id ?? null,
            ]);
            return;
        }

        Log::info('Mensagem recebida normalizada', [
            'original'     => $body,
            'normalizada'  => $normalized,
            'appointment'  => $appointment->id,
            'status'       => $appointment->status,
        ]);

        /**
         * TRATANDO "1" → CONFIRMAR / "2" → CANCELAR
         */
        $isConfirm = in_array($normalized, ['1', 'UM', 'SIM', 'OK', 'CONFIRMAR', 'CONCLUIR']);
        $isCancel  = in_array($normalized, ['2', 'DOIS', 'CANCELAR', 'NAO', 'NÃO', 'CANCEL']);

        if ($isConfirm) {
            $appointment->update(['status' => 'confirmado']);

            Log::info('Compromisso confirmado via WhatsApp', [
                'user_id'       => $user->id,
                'appointment_id' => $appointment->id,
            ]);

            try {
                $this->sendText($from, "Seu atendimento foi *CONFIRMADO* com sucesso!");
            } catch (\Exception $e) {
                Log::warning('Falha ao enviar mensagem de confirmação', [
                    'appointment_id' => $appointment->id,
                    'erro'           => $e->getMessage(),
                ]);
            }

            return;
        }

        if ($isCancel) {
            $appointment->update(['status' => 'cancelado']);

            Log::info('Compromisso cancelado via WhatsApp', [
                'user_id'       => $user->id,
                'appointment_id' => $appointment->id,
            ]);

            try {
                $this->sendText(
                    $from,
                    "Seu agendamento foi *CANCELADO*.

Deseja remarcar?
Responda *1* (Sim) ou *2* (Não)."
                );
            } catch (\Exception $e) {
                Log::warning('Falha ao enviar mensagem de cancelamento', [
                    'appointment_id' => $appointment->id,
                    'erro'           => $e->getMessage(),
                ]);
            }

            return;
        }

        Log::info('Mensagem ignorada (conteúdo não é comando reconhecido)', [
            'conteudo'       => $body,
            'normalizada'    => $normalized,
            'appointment_id' => $appointment->id,
        ]);
    }

    /**
     * Busca mensagens de um chat.
     */
    public function fetchChatMessages(string $number, ?string $direction = null, ?int $count = null): array
    {
        $payload = array_filter([
            'number'    => $this->normalizeNumber($number),
            'direction' => $direction,
            'count'     => $count,
        ]);

        $response = $this->post('getMessagesChat', $payload);
        return $response['data'] ?? $response;
    }

    /**
     * Requisições padrão para API Brasil.
     * Aqui ficam:
     * - headers
     * - logs de erro
     * - retry
     * - validações
     */
    private function post(string $endpoint, array $payload = []): array
    {
        $this->ensureCredentials();

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        // Proteção obrigatória multiempresa
        if (empty($this->config['device_token']) || empty($this->config['device_id'])) {
            Log::error('Nenhum device_token/device_id definido antes do POST');
            throw new RuntimeException('Device do WhatsAppService não configurado.');
        }

        $headers = [
            'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
            'DeviceToken'   => $this->config['device_token'],
            'DeviceId'      => $this->config['device_id'],
            'ProfileId'     => $this->config['profile_id'] ?? '',
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];

        $http = Http::withHeaders($headers)
            ->timeout(config('services.api_brasil.timeout', 25))
            ->connectTimeout(config('services.api_brasil.connect_timeout', 10));

        try {
            $response = $http->post($url, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            throw new RuntimeException('API Brasil não respondeu dentro do tempo limite.', 0, $exception);
        }

        if ($response->failed()) {
            $body = $response->json();
            $error = $body['message'] ?? $body['error'] ?? $response->body();

            Log::error('Erro enviado pela API Brasil', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'error'    => $error,
                'body'     => $body,
            ]);

            throw new RuntimeException("API Brasil retornou erro {$response->status()}: {$error}");
        }

        return $response->json() ?? [];
    }

    /**
     * Garante que token + device_token estão configurados.
     */
    private function ensureCredentials(): void
    {
        if (empty($this->config['token']) || empty($this->config['device_token'])) {
            throw new RuntimeException('Credenciais da API Brasil/WhatsApp não configuradas.');
        }

        if (empty($this->config['device_id'])) {
            throw new RuntimeException('Device ID da API Brasil não configurado.');
        }
    }

    /**
     * Normaliza número de WhatsApp.
     * Remove caracteres, garante prefixo 55 e dígito 9.
     */
    private function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        if (strlen($digits) === 12) {
            $digits = substr($digits, 0, 4) . '9' . substr($digits, 4);
        }

        if (strlen($digits) < 12) {
            throw new RuntimeException('Número de WhatsApp inválido: ' . $number);
        }

        return $digits;
    }

    /**
     * Cria novo device na API Brasil usando token mestre.
     */
    public function createDevice(string $deviceName): array
    {
        try {
            Log::info('Criando device na API Brasil', [
                'device_name' => $deviceName,
            ]);

            $url = $this->baseUrl . '/start';

            $headers = [
                'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
                'ProfileId'     => $this->config['profile_id'] ?? '',
                'Content-Type'  => 'application/json',
            ];

            $payload = [
                'powered_by'  => config('app.name', 'Agendoo'),
                'device_name' => $deviceName,
            ];

            $response = Http::withHeaders($headers)->post($url, $payload);
            $data     = $response->json();

            if ($response->failed()) {
                $error = $data['message'] ?? $data['error'] ?? $response->body();
                throw new RuntimeException('Erro ao criar device: ' . $error);
            }

            return [
                'device_token'  => data_get($data, 'device_token') ??
                    data_get($data, 'response.device_token'),
                'device_id'     => data_get($data, 'device_id') ??
                    data_get($data, 'response.device_id'),
                'full_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao criar device', ['erro' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Busca QR Code de login do device.
     */
    public function getDeviceQrCode(string $deviceToken): ?string
    {
        $url = $this->baseUrl . '/qrcode';

        $headers = [
            'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
            'DeviceToken'   => $deviceToken,
            'Content-Type'  => 'application/json',
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, []);
            $data = $response->json();

            return $data['qrcode']
                ?? $data['data']['qrcode']
                ?? $data['response']['qrcode']
                ?? null;
        } catch (\Exception $e) {
            Log::error('Erro ao obter QR Code', ['erro' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Verifica status de conexão do device.
     */
    public function checkDeviceStatus(string $deviceToken): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
            'DeviceToken'   => $deviceToken,
            'ProfileId'     => $this->config['profile_id'] ?? '',
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];

        try {
            /**
             * 1) TESTE via sendText (fallback oficial para detectar conexão)
             *    - Se enviar OU retornar erro normal → device está ONLINE
             *    - Apenas erros explícitos de "offline/desconectado" indicam desconexão real
             */
            $testNumber = $this->config['test_number'] ?? '550000000000';

            $response = Http::withHeaders($headers)
                ->timeout(8)
                ->connectTimeout(5)
                ->post($this->baseUrl . '/sendText', [
                    'number' => $testNumber,
                    'text'   => '__ping__'
                ]);

            $json = $response->json() ?? [];
            $msg  = strtolower(json_encode($json));

            // ✔ ONLINE SE:
            // - SendText retornou sucesso
            // - Ou retornou erro normal (número inválido, não é WhatsApp, etc.)
            if ($response->ok() && !isset($json['error'])) {
                return [
                    'connected'     => true,
                    'reason'        => 'sendText_success',
                    'full_response' => $json
                ];
            }

            // Se NÃO contiver as palavras que indicam realmente "desconexão"
            $errosOffline = [
                'offline',
                'desconectado',
                'device disconnected',
                'device offline',
                'session not found',
                'session_closed',
                'session_disconnected'
            ];

            $isOffline = false;
            foreach ($errosOffline as $termo) {
                if (str_contains($msg, $termo)) {
                    $isOffline = true;
                    break;
                }
            }

            if (!$isOffline) {
                // ✔ ONLINE mesmo com erro (número inválido, ação inexistente, etc.)
                return [
                    'connected'     => true,
                    'reason'        => 'sendText_error_but_device_online',
                    'full_response' => $json
                ];
            }

            /**
             * 2) ❌ Se chegou aqui → erro explícito de “offline”
             */
            return [
                'connected'     => false,
                'reason'        => 'device_offline_detected',
                'full_response' => $json
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status por fallback', [
                'erro' => $e->getMessage(),
            ]);

            return [
                'connected' => false,
                'reason'    => 'exception',
                'error'     => $e->getMessage()
            ];
        }
    }



    /**
     * Define credenciais do service para um device específico.
     */
    public function setDeviceCredentials(?string $deviceToken, ?string $deviceId = null): void
    {
        if ($deviceToken) $this->config['device_token'] = $deviceToken;
        if ($deviceId)    $this->config['device_id']    = $deviceId;
    }

    /**
     * Usa credenciais de um usuário (empresa).
     */
    public function useUserCredentials(User $user): void
    {
        // Se for cliente, usa empresa pai
        if ($user->tipo === 'cliente' && $user->user_id) {
            $user = User::find($user->user_id);
        }

        // Se é empresa e tem device configurado
        if ($user && $user->tipo === 'empresa' && $user->apibrasil_device_token) {

            if (empty($user->apibrasil_device_id)) {
                Log::warning('Empresa sem device_id configurado', [
                    'empresa_id' => $user->id,
                    'nome'       => $user->name,
                ]);
                return;
            }

            $this->setDeviceCredentials(
                $user->apibrasil_device_token,
                $user->apibrasil_device_id
            );

            Log::info('Usando credenciais da empresa', [
                'empresa_id' => $user->id,
                'empresa'    => $user->name,
            ]);

            return;
        }

        // Fallback – ainda existe aqui, pois tu pediu APENAS limpeza.
        Log::warning('Usando credenciais padrão do .env', [
            'user_id' => $user->id ?? null,
            'tipo'    => $user->tipo ?? null,
        ]);
    }

    /**
     * PEQUENA função auxiliar usada no início de envios.
     */
    private function assertDeviceConfigured(): void
    {
        if (empty($this->config['device_token']) || empty($this->config['device_id'])) {
            throw new RuntimeException('Credenciais de WhatsApp não definidas para este envio.');
        }
    }
}
