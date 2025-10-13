<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

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

    /**
     * Envia uma mensagem de texto simples.
     */
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
     * Envia um conjunto simples de botoes interativos.
     *
     * @param  array<int, array{id?: string, text: string}>  $buttons
     */
    public function sendButtons(string $number, string $text, array $buttons, array $options = []): array
    {
        if (empty($buttons)) {
            throw new RuntimeException('Defina ao menos um botao para a mensagem interativa.');
        }

        $payload = [
            'number' => $this->normalizeNumber($number),
            'text' => $text,
            'options' => array_merge([
                'useTemplateButtons' => true,
                'buttons' => array_map(function (array $button) {
                    $label = trim($button['text'] ?? '');

                    if ($label === '') {
                        throw new RuntimeException('Texto do botao interativo obrigatorio.');
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
     * Recupera novas mensagens (não lidas).
     */
    public function fetchNewMessages(): array
    {
        $response = $this->post('getAllNewMessages');

        return $response['data'] ?? $response;
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
     * Executa a chamada para a API Brasil.
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
            ->retry(config('services.api_brasil.retry_times', 1), config('services.api_brasil.retry_sleep', 1000));

        try {
            $response = $http->post($url, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            throw new RuntimeException('API Brasil nao respondeu dentro do tempo limite.', 0, $exception);
        }

        if ($response->failed()) {
            $body = $response->json();
            $error = $body['message'] ?? $body['error'] ?? $response->body();

            throw new RuntimeException('API Brasil retornou erro: ' . $error);
        }

        return $response->json() ?? [];
    }

    private function ensureCredentials(): void
    {
        if (empty($this->config['token']) || empty($this->config['device_token'])) {
            throw new RuntimeException('Credenciais do API Brasil/WhatsApp nao configuradas.');
        }
    }

    private function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if ($digits === '') {
            throw new RuntimeException('Numero de WhatsApp invalido.');
        }

        return $digits;
    }
}
