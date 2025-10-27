<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use App\Models\User;
use App\Models\Appointment;
use App\Models\ChatbotMessage;

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
     * Inicia uma sessÃ£o (gera QRCode quando necessÃ¡rio).
     */
    public function startSession(?string $deviceName = null, ?string $number = null, ?int $autoCloseMs = null): array
    {
        $payload = array_filter([
            'powered_by' => config('app.name', 'Agendoo'),
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
     * Envia uma lista de opÃ§Ãµes (botÃµes) para o usuÃ¡rio.
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
     * Envia um conjunto simples de botÃµes interativos.
     */
    public function sendButtons(string $number, string $text, array $buttons, array $options = []): array
    {
        if (empty($buttons)) {
            throw new RuntimeException('Defina ao menos um botÃ£o para a mensagem interativa.');
        }

        $payload = [
            'number' => $this->normalizeNumber($number),
            'text' => $text,
            'options' => array_merge([
                'useTemplateButtons' => true,
                'buttons' => array_map(function (array $button) {
                    $label = trim($button['text'] ?? '');
                    if ($label === '') {
                        throw new RuntimeException('Texto do botÃ£o interativo obrigatÃ³rio.');
                    }
                    return $button;
                }, $buttons),
            ], $options),
        ];

        return $this->post('sendButtons', $payload);
    }

    /**
     * Responde a uma mensagem especÃ­fica.
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
     * ðŸ”¹ Busca novas mensagens (nÃ£o lidas) e processa as respostas ("1" e "2")
     */
    public function fetchNewMessagesAndProcess(): void
    {
        $response = $this->post('getAllNewMessages');
        $data = $response['response']['contacts'] ?? [];

        if (empty($data)) {
            Log::info('ðŸ“­ Nenhuma nova mensagem recebida.');
            return;
        }

        foreach ($data as $msg) {
            $fromRaw = data_get($msg, 'from', '');
            $body = trim((string) data_get($msg, 'body', ''));

            // ðŸš« Ignorar grupos, broadcasts, status, comunidades, etc.
            if (
                str_contains($fromRaw, '@g.us') ||
                str_contains($fromRaw, '@broadcast') ||
                str_contains($fromRaw, '@status') ||
                str_contains($fromRaw, '@newsletter')
            ) {
                Log::info('ðŸ“¢ Ignorando mensagem de grupo/broadcast/newsletter', ['from' => $fromRaw]);
                continue;
            }

            // ðŸ”¹ Extrai sÃ³ os dÃ­gitos numÃ©ricos
            $from = preg_replace('/\D+/', '', $fromRaw);

            // ðŸš« Bloqueia nÃºmeros absurdamente longos (grupos com ID numÃ©rico)
            if (strlen($from) > 13 || strlen($from) < 11) {
                Log::info('ðŸš« Ignorando nÃºmero invÃ¡lido detectado', ['from' => $fromRaw, 'length' => strlen($from)]);
                continue;
            }

            // ðŸ”§ Normaliza o nÃºmero (adiciona prefixo 55 e o 9Âº dÃ­gito se necessÃ¡rio)
            if (!str_starts_with($from, '55')) {
                $from = '55' . $from;
            }

            // Adiciona o 9 apÃ³s o DDD se faltar (12 dÃ­gitos â†’ 13 dÃ­gitos)
            if (strlen($from) === 12) {
                $from = substr($from, 0, 4) . '9' . substr($from, 4);
            }

            // ðŸ§© SÃ³ processa se existir corpo da mensagem
            if ($from && $body !== '') {
                $this->processIncomingMessage($from, $body, $msg);
            }

            // ðŸ’¤ Pequena pausa entre mensagens (evita flood)
            usleep(300000); // 0.3s
        }
    }

    /**
     * ðŸ”¹ Processa cada mensagem recebida
     */
    public function processIncomingMessage(string $from, string $body, array $payload): void
    {
        // ðŸ”¹ Extrai ID Ãºnico da mensagem (API Brasil envia isso em vÃ¡rios nÃ­veis)
        $externalId =
            data_get($payload, 'id') ??
            data_get($payload, 'message.id') ??
            data_get($payload, 'data.id') ??
            data_get($payload, 'response.id') ??
            Str::uuid()->toString(); // fallback seguro

        // ðŸ”¹ Evita processar a mesma mensagem duas vezes
        if (ChatbotMessage::where('external_id', $externalId)->exists()) {
            Log::info('âš ï¸ Mensagem duplicada ignorada (jÃ¡ processada)', [
                'from' => $from,
                'id' => $externalId,
            ]);
            return;
        }

        // ðŸ”¹ Busca o usuÃ¡rio (empresa ou cliente vinculado)
        $user = User::where('whatsapp_number', $from)->first();

        if (!$user) {
            $empresa = User::whereHas('clientes', function ($query) use ($from) {
                $query->where('whatsapp_number', $from);
            })->first();

            if ($empresa) {
                $user = $empresa->clientes()->where('whatsapp_number', $from)->first();
            }
        }

        // ðŸ”¹ Registra a mensagem recebida no histÃ³rico
        ChatbotMessage::create([
            'user_id' => $user?->id,
            'whatsapp_numero' => $from,
            'direcao' => 'entrada',
            'conteudo' => $body,
            'payload' => $payload,
            'external_id' => $externalId, // ðŸ”¸ novo campo
        ]);

        if (!$user) {
            Log::warning('ðŸš« Mensagem recebida de nÃºmero nÃ£o registrado', ['from' => $from]);
            return;
        }

        // ðŸ”¹ Normaliza corpo da mensagem
        $normalized = strtoupper(trim($body));
        $normalized = preg_replace('/[\s\n\r\t\x{200B}-\x{200D}\x{FEFF}]+/u', '', $normalized);
        $normalized = str_replace(['ï¸âƒ£', 'âƒ£', 'âœ–ï¸', 'âœ”ï¸', '1ï¸âƒ£', '2ï¸âƒ£'], '', $normalized);
        $normalized = preg_replace('/[^\p{L}\p{N}]/u', '', $normalized);

        if (str_starts_with($normalized, '1')) {
            $normalized = '1';
        } elseif (str_starts_with($normalized, '2')) {
            $normalized = '2';
        }

        // ðŸ”¹ Busca compromisso pendente/confirmado vinculado ao usuÃ¡rio
        $appointment = Appointment::query()
            ->where(function ($query) use ($user, $from) {
                if ($user->tipo === 'cliente') {
                    $query->where('destinatario_user_id', $user->id);
                } else {
                    $query->where('user_id', $user->id)
                        ->orWhere('whatsapp_numero', $from);
                }
            })
            ->whereIn('status', ['pendente', 'confirmado', 'cancelado'])
            ->latest('inicio')
            ->first();

        if (! $appointment) {
            Log::info('âš ï¸ Nenhum compromisso pendente encontrado para este usuÃ¡rio.', [
                'whatsapp' => $from,
                'user_id' => $user->id ?? null,
            ]);
            return;
        }

        Log::info('ðŸ“© Mensagem recebida normalizada', [
            'original' => $body,
            'normalizada' => $normalized,
            'appointment_id' => $appointment->id,
            'status_atual' => $appointment->status,
        ]);

        if ($appointment->status === 'cancelado') {
            $wantsReschedule = ['SIM', 'S', 'YES', '1'];
            $doesNotWant = ['NAO', 'NÃƒO', 'N', 'NO', '2'];

            if (in_array($normalized, $wantsReschedule, true)) {
                // ðŸ”¹ Cliente respondeu SIM apÃ³s cancelamento
                $this->sendText($from, "Tudo certo! Em breve entraremos em contato para reagendar seu atendimento.");
                Log::info('ðŸ“… Cliente deseja remarcar apÃ³s cancelamento', [
                    'user_id' => $user->id,
                    'appointment_id' => $appointment->id,
                ]);
                return;
            }

            // if (in_array($normalized, $doesNotWant, true)) {
            //     // ðŸ”¹ Cliente respondeu NÃƒO apÃ³s cancelamento
            //     $this->sendText($from, "ðŸ‘‹ Obrigado! AtÃ© breve.");
            //     Log::info('ðŸ™Œ Cliente encerrou conversa apÃ³s cancelamento', [
            //         'user_id' => $user->id,
            //         'appointment_id' => $appointment->id,
            //     ]);
            //     return;
            // }
        }

        // ðŸ”¹ Interpreta comandos conhecidos
        $isConfirm = in_array($normalized, ['1', 'UM', 'CONFIRMAR', 'SIM', 'OK', 'CONCLUIR']);
        $isCancel  = in_array($normalized, ['2', 'DOIS', 'CANCELAR', 'NÃƒO', 'NAO', 'CANCEL']);

        Log::info('ðŸ” Verificando comando', [
            'normalized' => $normalized,
            'isConfirm' => $isConfirm,
            'isCancel' => $isCancel,
            'appointment_id' => $appointment->id,
            'status_atual' => $appointment->status,
        ]);

        if ($isConfirm) {
            $appointment->update(['status' => 'confirmado']);

            Log::info('âœ… Compromisso confirmado via WhatsApp', [
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
            ]);

            // Tenta enviar mensagem de confirmaÃ§Ã£o (mas nÃ£o bloqueia se der erro)
            try {
                $this->sendText($from, "âœ… Seu atendimento foi *CONFIRMADO* com sucesso!");
            } catch (\Exception $e) {
                Log::warning('âš ï¸ NÃ£o foi possÃ­vel enviar mensagem de confirmaÃ§Ã£o', [
                    'appointment_id' => $appointment->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        } elseif ($isCancel) {
            Log::info('ðŸ”¸ Entrando no cancelamento', [
                'appointment_id' => $appointment->id,
                'status_antes' => $appointment->status,
            ]);

            // ðŸ”¸ Atualiza o status para cancelado
            $appointment->update(['status' => 'cancelado']);

            Log::info('âŒ Compromisso cancelado via WhatsApp', [
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
            ]);

            // ðŸ”¸ Tenta enviar mensagem de cancelamento (mas nÃ£o bloqueia se der erro)
            try {
                $this->sendText($from, "âŒ Seu agendamento foi *CANCELADO*.\n\nDeseja remarcar? Responda *1* (Sim) ou *2* (NÃ£o).");
            } catch (\Exception $e) {
                Log::warning('âš ï¸ NÃ£o foi possÃ­vel enviar mensagem de cancelamento', [
                    'appointment_id' => $appointment->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        } else {
            Log::info('â„¹ï¸ Mensagem ignorada (nÃ£o Ã© comando conhecido)', [
                'conteudo' => $body,
                'normalizada' => $normalized,
                'appointment_id' => $appointment->id,
            ]);
        }
    }



    /**
     * Recupera as mensagens de um chat especÃ­fico.
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
     * ðŸ”§ ExecuÃ§Ã£o padrÃ£o de requisiÃ§Ã£o Ã  API Brasil
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
            throw new RuntimeException('API Brasil nÃ£o respondeu dentro do tempo limite.', 0, $exception);
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
            throw new RuntimeException('Credenciais da API Brasil/WhatsApp nÃ£o configuradas.');
        }

        if (empty($this->config['device_id'])) {
            Log::warning('Tentativa de acessar API Brasil sem device_id configurado.', [
                'device_token' => substr($this->config['device_token'] ?? '', 0, 8) . '...',
            ]);
            throw new RuntimeException('Device ID da API Brasil nÃ£o configurado.');
        }
    }

    private function normalizeNumber(string $number): string
    {
        // Remove tudo que nÃ£o for nÃºmero
        $digits = preg_replace('/\D+/', '', $number);

        // Garante que tenha o cÃ³digo do Brasil no inÃ­cio
        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        // Detecta DDD + nÃºmero com ou sem o 9
        // Exemplo: 555196244848 â†’ faltando o 9
        // Exemplo: 5551996244848 â†’ jÃ¡ tem o 9
        if (strlen($digits) === 12) {
            // Inserir o 9 apÃ³s o DDD (depois de 4 dÃ­gitos)
            $digits = substr($digits, 0, 4) . '9' . substr($digits, 4);
        }

        if ($digits === '' || strlen($digits) < 12) {
            throw new RuntimeException('NÃºmero de WhatsApp invÃ¡lido: ' . $number);
        }

        return $digits;
    }

    /**
     * Cria um novo device/sessÃ£o na API Brasil (usando token mestre)
     * Faz requisiÃ§Ã£o direta sem validar device_token (pois ainda nÃ£o existe)
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
                // NÃƒO envia DeviceToken aqui, pois estamos CRIANDO um novo
                'ProfileId' => $this->config['profile_id'] ?? '',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

            $payload = array_filter([
                'powered_by' => config('app.name', 'Agendoo'),
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
                throw new RuntimeException('API Brasil nÃ£o retornou device_token. Resposta: ' . json_encode($data));
            }

            return [
                'device_token' => $deviceToken,
                'device_id' => $deviceId,
                'full_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('ExceÃ§Ã£o ao criar device: ' . $e->getMessage(), [
                'exception' => get_class($e),
            ]);
            throw $e;
        }
    }

    /**
     * ObtÃ©m o QR Code de um device especÃ­fico
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
            Log::error('ExceÃ§Ã£o ao obter QR Code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica o status de conexÃ£o de um device especÃ­fico
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

            // ðŸ”¹ Mesmo com erro 401, a API Brasil retorna o status real do device
            if ($response->failed()) {
                Log::warning('API retornou erro ao verificar status, mas vamos checar a resposta', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // âœ… Extrai o status do device mesmo no erro
                $deviceStatus = $data['device']['status'] ?? null;

                // Status vÃ¡lidos de conexÃ£o: "inChat", "qrcode", "open", "connected"
                $statusesAtivos = ['inChat', 'qrcode', 'open', 'connected', 'isLogged'];

                if ($deviceStatus && in_array($deviceStatus, $statusesAtivos)) {
                    Log::info('âœ… Device estÃ¡ conectado (extraÃ­do da resposta de erro)', [
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

            // Verifica se estÃ¡ conectado baseado na resposta (quando nÃ£o hÃ¡ erro)
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
            Log::error('ExceÃ§Ã£o ao verificar status: ' . $e->getMessage());
            return ['connected' => false];
        }
    }

    /**
     * Configura o serviÃ§o para usar credenciais de um device especÃ­fico
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
     * Configura credenciais baseado em um usuÃ¡rio (empresa)
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
            if (empty($user->apibrasil_device_id)) {
                Log::warning('Empresa sem device_id configurado para WhatsApp.', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ]);
                return;
            }

            $this->setDeviceCredentials(
                $user->apibrasil_device_token,
                $user->apibrasil_device_id
            );

            Log::info('Usando credenciais da empresa', [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            return;
        }

        // Fallback: usa credenciais do .env (padrÃ£o)
        Log::warning('Usando credenciais padrÃ£o do .env', [
            'user_id' => $user->id ?? null,
            'tipo' => $user->tipo ?? null,
        ]);
    }
}

