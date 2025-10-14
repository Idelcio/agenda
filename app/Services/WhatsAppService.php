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
     * 🔹 Busca novas mensagens (não lidas) e processa as respostas ("1" e "2")
     */
    public function fetchNewMessagesAndProcess(): void
    {
        $response = $this->post('getAllNewMessages');
        $data = $response['response']['contacts'] ?? [];

        if (empty($data)) {
            Log::info('📭 Nenhuma nova mensagem recebida.');
            return;
        }

        foreach ($data as $msg) {
            $fromRaw = data_get($msg, 'from', '');
            $body = trim((string) data_get($msg, 'body', ''));

            // 🚫 Ignorar grupos, broadcasts, status, comunidades, etc.
            if (
                str_contains($fromRaw, '@g.us') ||
                str_contains($fromRaw, '@broadcast') ||
                str_contains($fromRaw, '@status') ||
                str_contains($fromRaw, '@newsletter')
            ) {
                Log::info('📢 Ignorando mensagem de grupo/broadcast/newsletter', ['from' => $fromRaw]);
                continue;
            }

            // 🔹 Extrai só os dígitos numéricos
            $from = preg_replace('/\D+/', '', $fromRaw);

            // 🚫 Bloqueia números absurdamente longos (grupos com ID numérico)
            if (strlen($from) > 13 || strlen($from) < 11) {
                Log::info('🚫 Ignorando número inválido detectado', ['from' => $fromRaw, 'length' => strlen($from)]);
                continue;
            }

            // 🔧 Normaliza o número (adiciona prefixo 55 e o 9º dígito se necessário)
            if (!str_starts_with($from, '55')) {
                $from = '55' . $from;
            }

            // Adiciona o 9 após o DDD se faltar (12 dígitos → 13 dígitos)
            if (strlen($from) === 12) {
                $from = substr($from, 0, 4) . '9' . substr($from, 4);
            }

            // 🧩 Só processa se existir corpo da mensagem
            if ($from && $body !== '') {
                $this->processIncomingMessage($from, $body, $msg);
            }

            // 💤 Pequena pausa entre mensagens (evita flood)
            usleep(300000); // 0.3s
        }
    }

    /**
     * 🔹 Processa cada mensagem recebida
     */
    public function processIncomingMessage(string $from, string $body, array $payload): void
    {
        // 🔹 Extrai ID único da mensagem (API Brasil envia isso em vários níveis)
        $externalId =
            data_get($payload, 'id') ??
            data_get($payload, 'message.id') ??
            data_get($payload, 'data.id') ??
            data_get($payload, 'response.id') ??
            Str::uuid()->toString(); // fallback seguro

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

        // 🔹 Busca compromisso pendente/confirmado vinculado ao usuário
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
            Log::info('⚠️ Nenhum compromisso pendente encontrado para este usuário.', [
                'whatsapp' => $from,
                'user_id' => $user->id ?? null,
            ]);
            return;
        }

        Log::info('📩 Mensagem recebida normalizada', [
            'original' => $body,
            'normalizada' => $normalized,
            'appointment_id' => $appointment->id,
            'status_atual' => $appointment->status,
        ]);

        if ($appointment->status === 'cancelado') {
            $wantsReschedule = ['SIM', 'S', 'YES', '1'];
            $doesNotWant = ['NAO', 'NÃO', 'N', 'NO', '2'];

            if (in_array($normalized, $wantsReschedule, true)) {
                // 🔹 Cliente respondeu SIM após cancelamento
                $this->sendText($from, "✅ Em breve entraremos em contato para reagendar seu atendimento.");
                Log::info('📅 Cliente deseja remarcar após cancelamento', [
                    'user_id' => $user->id,
                    'appointment_id' => $appointment->id,
                ]);
                return;
            }

            if (in_array($normalized, $doesNotWant, true)) {
                // 🔹 Cliente respondeu NÃO após cancelamento
                $this->sendText($from, "👋 Obrigado! Até breve.");
                Log::info('🙌 Cliente encerrou conversa após cancelamento', [
                    'user_id' => $user->id,
                    'appointment_id' => $appointment->id,
                ]);
                return;
            }
        }

        // 🔹 Interpreta comandos conhecidos
        $isConfirm = in_array($normalized, ['1', 'UM', 'CONFIRMAR', 'SIM', 'OK', 'CONCLUIR']);
        $isCancel  = in_array($normalized, ['2', 'DOIS', 'CANCELAR', 'NÃO', 'NAO', 'CANCEL']);

        if ($isConfirm) {
            $appointment->update(['status' => 'confirmado']);
            $this->sendText($from, "✅ Seu atendimento foi *CONFIRMADO* com sucesso!");
            Log::info('✅ Compromisso confirmado via WhatsApp', [
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
            ]);
        } elseif ($isCancel) {
            // 🔸 Atualiza o status para cancelado
            $appointment->update(['status' => 'cancelado']);

            // 🔸 Envia mensagem de cancelamento com opções
            $this->sendText($from, "❌ Seu agendamento foi *CANCELADO*.\n\nDeseja remarcar? Responda *1* (Sim) ou *2* (Não).");

            Log::info('❌ Compromisso cancelado via WhatsApp', [
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
            ]);
        } else {
            Log::info('ℹ️ Mensagem ignorada (não é comando conhecido)', [
                'conteudo' => $body,
                'normalizada' => $normalized,
                'appointment_id' => $appointment->id,
            ]);
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
}
