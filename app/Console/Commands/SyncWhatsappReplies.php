<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SyncWhatsappReplies extends Command
{
    protected $signature = 'agenda:sincronizar-respostas';

    protected $description = 'Busca novas mensagens no WhatsApp e atualiza a agenda conforme respostas dos clientes.';

    public function __construct(private WhatsAppService $whatsApp)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $messages = $this->whatsApp->fetchNewMessages();
        } catch (RuntimeException $exception) {
            $this->error('Nao foi possivel consultar mensagens: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        if (empty($messages)) {
            $this->info('Nenhuma mensagem nova encontrada.');

            return Command::SUCCESS;
        }

        $processed = 0;

        foreach ($messages as $message) {
            $record = $this->storeIncomingMessage($message);

            if ($record && $record->appointment_id) {
                $this->applyInteractiveResponse($record);
            }

            $processed++;
        }

        $this->info("Mensagens processadas: {$processed}");

        return Command::SUCCESS;
    }

    private function storeIncomingMessage(array $payload): ?WhatsAppMessage
    {
        $message = $payload['message'] ?? $payload;
        $phone = $this->normalizePhone(
            $payload['number'] ?? $payload['phone'] ?? $payload['from'] ?? data_get($message, 'from')
        );

        if (! $phone) {
            Log::warning('Mensagem do WhatsApp sem numero identificado', ['payload' => $payload]);

            return null;
        }

        $externalId = data_get($message, 'id') ?? data_get($payload, 'id');
        $contextId = data_get($message, 'context.id') ?? data_get($payload, 'context_id');
        $type = data_get($message, 'type') ?? data_get($payload, 'type') ?? 'text';

        $record = WhatsAppMessage::updateOrCreate(
            [
                'external_id' => $externalId,
            ],
            [
                'direction' => 'received',
                'type' => $type,
                'phone' => $phone,
                'payload' => $payload,
                'context_id' => $contextId,
                'status' => data_get($payload, 'status'),
            ]
        );

        if ($contextId) {
            $related = WhatsAppMessage::where('external_id', $contextId)->first();

            if ($related) {
                $record->appointment_id = $related->appointment_id;
                $record->user_id = $related->user_id;
                $record->save();
            }
        }

        return $record;
    }

    private function applyInteractiveResponse(WhatsAppMessage $message): void
    {
        $payload = $message->payload ?? [];
        $selectedRow = data_get($payload, 'message.listResponse.singleSelectReply.selectedRowId')
            ?? data_get($payload, 'message.interactive.single_select_reply.selected_row_id')
            ?? data_get($payload, 'selectedRowId');

        $selectedButton = data_get($payload, 'message.buttonResponse.buttonReply.id')
            ?? data_get($payload, 'message.buttonResponse.buttonReply.selectedButtonId')
            ?? data_get($payload, 'message.interactive.button_reply.id')
            ?? data_get($payload, 'message.interactive.button_reply.button_id')
            ?? data_get($payload, 'message.buttonsResponseMessage.selectedButtonId')
            ?? data_get($payload, 'buttonReply.id')
            ?? data_get($payload, 'buttonReply.selectedButtonId')
            ?? data_get($payload, 'button_reply.id')
            ?? data_get($payload, 'button_reply.selected_button_id');

        $selection = $selectedRow ?? $selectedButton;

        if (! $selection) {
            return;
        }

        $appointment = Appointment::find($message->appointment_id);

        if (! $appointment) {
            return;
        }

        $normalized = strtolower((string) $selection);

        if ($normalized === 'confirm') {
            $appointment->status = 'confirmado';
            $appointment->save();
            $message->status = 'confirmado';
        } elseif ($normalized === 'cancel') {
            $appointment->status = 'cancelado';
            $appointment->save();
            $message->status = 'cancelado';
        }

        $message->processed_at = now();
        $message->payload = array_merge(
            $message->payload ?? [],
            [
                'selected_row' => $selectedRow,
                'selected_button' => $selectedButton,
            ]
        );
        $message->save();
    }

    private function normalizePhone(?string $number): ?string
    {
        if (! $number) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        return $digits ?: null;
    }
}
