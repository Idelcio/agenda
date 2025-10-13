<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\User;
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
            $messages = $this->whatsApp->fetchNewMessagesAndProcess();
        } catch (RuntimeException $exception) {
            $this->error('Não foi possível consultar mensagens: ' . $exception->getMessage());
            return Command::FAILURE;
        }

        if (empty($messages)) {
            $this->info('Nenhuma mensagem nova encontrada.');
            return Command::SUCCESS;
        }

        $processed = 0;

        foreach ($messages as $message) {
            if (!is_array($message)) {
                Log::warning('❌ Payload inválido ao sincronizar mensagens WhatsApp', [
                    'conteudo' => $message,
                ]);
                continue;
            }

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
        if (empty($payload) || !is_array($payload)) {
            Log::warning('❌ Payload inválido ao sincronizar mensagens WhatsApp', ['conteudo' => $payload]);
            return null;
        }

        $message = $payload['message'] ?? $payload;

        // 🔹 Extrai número de vários possíveis locais
        $rawPhone = data_get($payload, 'number')
            ?? data_get($payload, 'phone')
            ?? data_get($payload, 'from')
            ?? data_get($payload, 'messages.0.from')
            ?? data_get($payload, 'contacts.0.id')
            ?? data_get($payload, 'data.data.from')
            ?? data_get($message, 'from');

        // 🔹 Normaliza com a mesma lógica usada no WhatsAppService
        $phone = $this->normalizePhone($rawPhone);

        if (!$phone) {
            Log::warning('Mensagem do WhatsApp sem número identificado', ['payload' => $payload]);
            return null;
        }

        // 🔹 Tenta vincular o número a um usuário
        $user = \App\Models\User::where('whatsapp_number', $phone)
            ->orWhereHas('clientes', function ($query) use ($phone) {
                $query->where('whatsapp_number', $phone);
            })
            ->first();


        if (!$user) {
            Log::warning('🚫 Mensagem recebida de número não registrado', ['from' => $phone]);
            return null; // <- importante!
        }


        $externalId = data_get($message, 'id') ?? data_get($payload, 'id');
        $contextId  = data_get($message, 'context.id') ?? data_get($payload, 'context_id');
        $type       = data_get($message, 'type') ?? data_get($payload, 'type') ?? 'text';

        $record = WhatsAppMessage::updateOrCreate(
            ['external_id' => $externalId],
            [
                'direction'  => 'received',
                'type'       => $type,
                'phone'      => $phone,
                'payload'    => $payload,
                'context_id' => $contextId,
                'status'     => data_get($payload, 'status'),
                'user_id'    => $user?->id,
            ]
        );

        // 🔹 Relaciona com compromisso anterior, se possível
        if ($contextId) {
            $related = WhatsAppMessage::where('external_id', $contextId)->first();

            if ($related) {
                $record->appointment_id = $related->appointment_id;
                $record->user_id        = $related->user_id ?? $user?->id;
                $record->save();
            }
        }

        return $record;
    }

    private function applyInteractiveResponse(WhatsAppMessage $message): void
    {
        $appointment = Appointment::find($message->appointment_id);
        if (!$appointment) {
            return;
        }

        $payload = $message->payload ?? [];

        $selectedRow = data_get($payload, 'message.listResponse.singleSelectReply.selectedRowId')
            ?? data_get($payload, 'message.interactive.single_select_reply.selected_row_id')
            ?? data_get($payload, 'selectedRowId');

        $selectedButton = data_get($payload, 'message.buttonResponse.buttonReply.id')
            ?? data_get($payload, 'message.interactive.button_reply.id')
            ?? data_get($payload, 'buttonReply.id')
            ?? data_get($payload, 'button_reply.id');

        $selection = $selectedRow ?? $selectedButton;

        $textReply = strtolower(trim(
            data_get($payload, 'message.conversation')
                ?? data_get($payload, 'message.text')
                ?? data_get($payload, 'text', '')
        ));

        if ($textReply === '1' || $textReply === 'confirmar' || $selection === 'confirm' || $selection === '1') {
            $appointment->update(['status' => 'concluido']);
            $message->status = 'concluido';
            Log::info('✅ Compromisso concluído via resposta WhatsApp', ['appointment_id' => $appointment->id]);
        } elseif ($textReply === '2' || $textReply === 'cancelar' || $selection === 'cancel' || $selection === '2') {
            $appointment->update(['status' => 'cancelado']);
            $message->status = 'cancelado';
            Log::info('❌ Compromisso cancelado via resposta WhatsApp', ['appointment_id' => $appointment->id]);
        } else {
            if (!empty($textReply)) {
                $appointment->update(['status' => 'concluido']);
                Log::info('ℹ️ Compromisso marcado como concluído (resposta genérica)', [
                    'appointment_id' => $appointment->id,
                    'resposta' => $textReply,
                ]);
            }
        }

        $message->processed_at = now();
        $message->save();
    }

    private function normalizePhone(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        if ($digits === '') {
            return null;
        }

        // Garante prefixo do Brasil
        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        // Adiciona o 9 após o DDD se faltar
        if (strlen($digits) === 12) {
            $digits = substr($digits, 0, 4) . '9' . substr($digits, 4);
        }

        return $digits;
    }
}
