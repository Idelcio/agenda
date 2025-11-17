<?php

namespace App\Console\Commands;

use App\Jobs\SendMassMessageJob;
use App\Jobs\SendMediaMessageJob;
use App\Models\MassMessage;
use App\Models\MediaMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa mensagens agendadas (texto e mídia) que estão prontas para envio';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando mensagens agendadas...');

        // Processa mensagens de texto agendadas
        $textMessages = MassMessage::where('status', 'pendente')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->get();

        foreach ($textMessages as $message) {
            $empresa = $message->user;

            if (! $empresa || ! $empresa->apibrasil_device_token) {
                Log::warning('⚠️ MassMessage ignorada — empresa sem credenciais', [
                    'mass_message_id' => $message->id,
                    'empresa_id' => $empresa->id ?? null,
                ]);
                continue;
            }

            $this->info("Disparando envio de mensagem em massa (texto): {$message->titulo} ({$empresa->name})");
            Log::info('📡 Enfileirando envio em massa para empresa correta', [
                'mass_message_id' => $message->id,
                'empresa_id' => $empresa->id,
                'empresa_nome' => $empresa->name,
            ]);

            SendMassMessageJob::dispatch($message->id);

            Log::info('Mensagem em massa agendada disparada', [
                'mass_message_id' => $message->id,
                'titulo' => $message->titulo,
                'empresa_id' => $empresa->id,
                'scheduled_for' => $message->scheduled_for,
            ]);
        }

        // Processa mensagens com mídia agendadas
        $mediaMessages = MediaMessage::where('status', 'pendente')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->get();

        foreach ($mediaMessages as $message) {
            $empresa = $message->user;

            if (! $empresa || ! $empresa->apibrasil_device_token) {
                Log::warning('⚠️ MediaMessage ignorada — empresa sem credenciais', [
                    'media_message_id' => $message->id,
                    'empresa_id' => $empresa->id ?? null,
                ]);
                continue;
            }

            $this->info("Disparando envio de mensagem em massa (mídia): {$message->titulo} ({$empresa->name})");
            Log::info('📡 Enfileirando envio de mídia para empresa correta', [
                'media_message_id' => $message->id,
                'empresa_id' => $empresa->id,
                'empresa_nome' => $empresa->name,
            ]);

            SendMediaMessageJob::dispatch($message->id);

            Log::info('Mensagem com mídia agendada disparada', [
                'media_message_id' => $message->id,
                'titulo' => $message->titulo,
                'empresa_id' => $empresa->id,
                'scheduled_for' => $message->scheduled_for,
            ]);
        }

        $total = $textMessages->count() + $mediaMessages->count();
        $this->info("Total de {$total} mensagem(ns) agendada(s) processada(s).");

        return Command::SUCCESS;
    }
}
