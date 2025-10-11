<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class WhatsAppReminderService
{
    public function __construct(private WhatsAppService $whatsApp)
    {
    }

    /**
     * Envia mensagem de lembrete com op����es de confirma��ǜo/cancelamento.
     *
     * @throws RuntimeException
     */
    public function sendAppointmentReminder(Appointment $appointment): void
    {
        if (! $appointment->notificar_whatsapp) {
            throw new RuntimeException('Notificacoes por WhatsApp desativadas para este compromisso.');
        }

        $destino = $appointment->whatsapp_numero ?? $appointment->user->whatsapp_number;

        if (! $destino) {
            throw new RuntimeException('Informe um numero de WhatsApp valido no perfil ou no compromisso.');
        }

        $mensagem = sprintf(
            'Ola! Voce tem um agendamento de %s em %s.',
            $appointment->titulo,
            $appointment->inicio?->timezone(config('app.timezone'))->format('d/m/Y \\a\\s H:i')
        );

        $textResponse = $this->whatsApp->sendText($destino, $mensagem);
        $this->storeWhatsappMessage(
            $appointment,
            'sent',
            'text',
            $destino,
            [
                'message' => $mensagem,
                'response' => $textResponse,
            ],
            $this->extractMessageId($textResponse)
        );

        $buttons = $this->buildConfirmationButtons($appointment);
        $buttonResponse = $this->whatsApp->sendButtons(
            $destino,
            'Confirme ou cancele este horario.',
            $buttons,
            [
                'time_typing' => 1200,
                'title' => 'Confirme sua presenca',
                'footer' => $appointment->inicio?->timezone(config('app.timezone'))->format('d/m/Y \\a\\s H:i'),
                'use_template_buttons' => false,
            ]
        );

        $this->storeWhatsappMessage(
            $appointment,
            'sent',
            'buttons',
            $destino,
            [
                'message' => 'Confirme ou cancele o agendamento.',
                'buttons' => $buttons,
                'response' => $buttonResponse,
            ],
            $this->extractMessageId($buttonResponse)
        );

        $appointment->markAsReminded();
    }

    /**
     * Envia mensagem manual (com ou sem anexo).
     *
     * @throws RuntimeException
     */
    public function sendQuickMessage(
        ?Appointment $appointment,
        string $destino,
        ?string $mensagem = null,
        ?UploadedFile $attachment = null,
        ?int $userId = null
    ): void {
        if ($attachment) {
            $base64 = $this->encodeAttachment($attachment);
            $mediaResponse = $this->whatsApp->sendMediaFromBase64($destino, $base64, $mensagem ?: null);

            $this->storeWhatsappMessage(
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

            return;
        }

        if ($mensagem === null || trim($mensagem) === '') {
            throw new RuntimeException('Informe uma mensagem ou anexe um arquivo.');
        }

        $textResponse = $this->whatsApp->sendText($destino, $mensagem);

        $this->storeWhatsappMessage(
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
    }

    private function buildConfirmationButtons(Appointment $appointment): array
    {
        return [
            [
                'id' => 'confirm',
                'text' => 'Confirmar',
            ],
            [
                'id' => 'cancel',
                'text' => 'Cancelar',
            ],
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
