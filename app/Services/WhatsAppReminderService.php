<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class WhatsAppReminderService
{
    public function __construct(private WhatsAppService $whatsApp) {}

    /**
     * Envia mensagem de lembrete com opções de confirmação/cancelamento.
     */
    public function sendAppointmentReminder(Appointment $appointment): void
    {
        if (! $appointment->notificar_whatsapp) {
            throw new RuntimeException('Notificações por WhatsApp desativadas para este compromisso.');
        }

        // Configura as credenciais da empresa antes de enviar
        $this->whatsApp->useUserCredentials($appointment->user);

        $destino = $appointment->whatsapp_numero ?? $appointment->user->whatsapp_number;

        if (! $destino) {
            throw new RuntimeException('Informe um número de WhatsApp válido no perfil ou no compromisso.');
        }

        $mensagem = $appointment->whatsapp_mensagem
            ?: sprintf(
                'Olá! Você tem um agendamento de %s em %s.',
                $appointment->titulo,
                $appointment->inicio?->timezone(config('app.timezone'))->format('d/m/Y \\a\\s H:i')
            );

        // Verifica o tipo de mensagem para decidir se envia botões
        $tipoMensagem = $appointment->tipo_mensagem ?? 'compromisso';

        Log::info('📩 Enviando lembrete', [
            'appointment_id' => $appointment->id,
            'tipo_mensagem' => $tipoMensagem,
            'titulo' => $appointment->titulo,
        ]);

        if ($tipoMensagem === 'compromisso') {
            // Adiciona instruções para resposta 1 ou 2 apenas para compromissos
            $mensagem .= "\n\n*Responda:*\n✅ Digite *1* para CONFIRMAR\n❌ Digite *2* para CANCELAR";
        }
        // Se for tipo 'aviso', não adiciona os botões - mensagem apenas informativa

        $this->sendQuickMessage(
            $appointment,
            $destino,
            $mensagem,
            userId: $appointment->user_id,
            withConfirmationButtons: false
        );

        $appointment->markAsReminded();
    }

    /**
     * Envia mensagem manual (com ou sem anexo).
     */
    public function sendQuickMessage(
        ?Appointment $appointment,
        string $destino,
        ?string $mensagem = null,
        ?UploadedFile $attachment = null,
        ?int $userId = null,
        bool $withConfirmationButtons = false
    ): WhatsAppMessage {
        // Configura credenciais da empresa (se houver appointment ou userId)
        if ($appointment && $appointment->user) {
            $this->whatsApp->useUserCredentials($appointment->user);
        } elseif ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $this->whatsApp->useUserCredentials($user);
            }
        }

        if ($attachment) {
            $base64 = $this->encodeAttachment($attachment);
            $mediaResponse = $this->whatsApp->sendMediaFromBase64($destino, $base64, $mensagem ?: null);

            $mediaMessage = $this->storeWhatsappMessage(
                $appointment,
                'sent',
                'media',
                $destino,
                [
                    'message' => $mensagem,
                    'response' => $mediaResponse,
                ],
                $this->extractMessageId($mediaResponse),
                userId: $userId
            );

            if ($withConfirmationButtons) {
                $this->sendConfirmationButtons($appointment, $destino, $mediaMessage, $userId);
            }

            return $mediaMessage;
        }

        if ($mensagem === null || trim($mensagem) === '') {
            throw new RuntimeException('Informe uma mensagem ou anexe um arquivo.');
        }

        $textResponse = $this->whatsApp->sendText($destino, $mensagem);

        $textMessage = $this->storeWhatsappMessage(
            $appointment,
            'sent',
            'text',
            $destino,
            [
                'message' => $mensagem,
                'response' => $textResponse,
            ],
            $this->extractMessageId($textResponse),
            userId: $userId
        );

        if ($withConfirmationButtons) {
            $this->sendConfirmationButtons($appointment, $destino, $textMessage, $userId);
        }

        return $textMessage;
    }

    /**
     * Processa automaticamente uma resposta recebida (1 ou 2).
     * Pode ser chamado diretamente no webhook da API Brasil.
     */
    public function handleIncomingReply(string $from, string $body, array $payload = []): void
    {
        try {
            Log::info('📨 Recebendo resposta via WhatsAppReminderService', [
                'from' => $from,
                'body' => $body,
            ]);

            $this->whatsApp->processIncomingMessage($from, $body, $payload);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao processar resposta do cliente via WhatsApp', [
                'from' => $from,
                'body' => $body,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function buildConfirmationButtons(): array
    {
        return [
            ['id' => '1', 'text' => '✅ 1 - CONFIRMAR'],
            ['id' => '2', 'text' => '❌ 2 - CANCELAR'],
        ];
    }

    private function storeWhatsappMessage(
        ?Appointment $appointment,
        string $direction,
        string $type,
        string $phone,
        array $payload,
        ?string $externalId = null,
        ?string $contextId = null,
        ?int $userId = null
    ): WhatsAppMessage {
        return WhatsAppMessage::create([
            'appointment_id' => $appointment?->id,
            'user_id' => $userId ?? $appointment?->user_id,
            'direction' => $direction,
            'type' => $type,
            'phone' => $this->normalizePhone($phone),
            'external_id' => $externalId,
            'context_id' => $contextId,
            'payload' => $payload,
        ]);
    }

    private function sendConfirmationButtons(
        ?Appointment $appointment,
        string $destino,
        WhatsAppMessage $originMessage,
        ?int $userId = null
    ): void {
        $buttons = $this->buildConfirmationButtons();
        $prompt = 'Responda com o número da sua escolha:';

        $options = [
            'useTemplateButtons' => true,
            'title' => '📋 Status do Compromisso',
            'footer' => $appointment && $appointment->inicio
                ? $appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y \\a\\s H:i')
                : 'Ou responda apenas 1 ou 2',
            'delay' => 0,
        ];

        try {
            $buttonResponse = $this->whatsApp->sendButtons($destino, $prompt, $buttons, $options);
        } catch (\Throwable $exception) {
            report($exception);
            return;
        }

        $stored = $this->storeWhatsappMessage(
            $appointment,
            'sent',
            'buttons',
            $destino,
            [
                'message' => $prompt,
                'buttons' => $buttons,
                'response' => $buttonResponse,
            ],
            $this->extractMessageId($buttonResponse),
            $originMessage->external_id,
            $userId ?? $originMessage->user_id
        );

        if (config('app.debug')) {
            Log::info('WhatsApp buttons response stored', [
                'buttons_response' => $buttonResponse,
                'stored_message_id' => $stored->id ?? null,
            ]);
        }
    }

    private function extractMessageId(array $response): ?string
    {
        return data_get($response, 'data.id')
            ?? data_get($response, 'data.messageId')
            ?? data_get($response, 'data.message_id')
            ?? data_get($response, 'messageId')
            ?? data_get($response, 'message.id')
            ?? null;
    }

    private function encodeAttachment(UploadedFile $file): string
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        return 'data:' . $mime . ';base64,' . base64_encode($file->get());
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }
}
