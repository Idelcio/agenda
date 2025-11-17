<?php

namespace App\Jobs;

use App\Models\MassMessage;
use App\Models\MassMessageItem;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMassMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $massMessageId;

    /**
     * Número de tentativas
     */
    public $tries = 3;

    /**
     * Timeout em segundos
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($massMessageId)
    {
        $this->massMessageId = $massMessageId;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsApp): void
    {
        $massMessage = MassMessage::find($this->massMessageId);

        if (!$massMessage) {
            Log::error('MassMessage não encontrada', ['id' => $this->massMessageId]);
            return;
        }

        // ---------------------------------------------------------------
        // 🔐 Carrega empresa dona da mensagem e seta credenciais
        // ---------------------------------------------------------------
        $empresa = $massMessage->user;

        if (!$empresa || !$empresa->apibrasil_device_token) {
            Log::warning('❌ Empresa sem credenciais de WhatsApp para envio em massa', [
                'empresa_id' => $empresa->id ?? null,
                'mass_message_id' => $massMessage->id,
            ]);
            return;
        }

        $whatsApp->setDeviceCredentials(
            $empresa->apibrasil_device_token,
            $empresa->apibrasil_device_id
        );

        $status = $whatsApp->checkDeviceStatus($empresa->apibrasil_device_token);
        if (!($status['connected'] ?? false)) {
            Log::warning('❌ Sessão da empresa NÃO está conectada para envio em massa', [
                'empresa_id' => $empresa->id,
                'empresa_nome' => $empresa->name,
                'mass_message_id' => $massMessage->id,
                'status' => $status,
            ]);
            return;
        }

        Log::info('📡 Enviando mensagem em massa usando a empresa correta', [
            'empresa_id' => $empresa->id,
            'empresa_nome' => $empresa->name,
            'mass_message_id' => $massMessage->id,
        ]);

        // Marca como processando
        $massMessage->update([
            'status' => 'processando',
            'iniciado_em' => now(),
        ]);

        // Pega todos os itens pendentes
        $items = $massMessage->items()->where('status', 'pendente')->get();

        Log::info('Iniciando envio em massa', [
            'mass_message_id' => $massMessage->id,
            'total_items' => $items->count(),
        ]);

        $enviados = 0;
        $falhas = 0;

        foreach ($items as $index => $item) {
            try {
                // Envia a mensagem
                $response = $whatsApp->sendMessage($item->telefone, $massMessage->mensagem);

                // Marca como enviado
                $item->update([
                    'status' => 'enviado',
                    'enviado_em' => now(),
                ]);

                $enviados++;

                Log::info('Mensagem enviada com sucesso', [
                    'item_id' => $item->id,
                    'telefone' => $item->telefone,
                ]);

                // Aguarda 5 segundos antes do próximo envio (exceto no último)
                if ($index < $items->count() - 1) {
                    sleep(5);
                }

            } catch (\Exception $e) {
                // Marca como erro
                $item->update([
                    'status' => 'erro',
                    'erro_mensagem' => $e->getMessage(),
                ]);

                $falhas++;

                Log::error('Erro ao enviar mensagem em massa', [
                    'item_id' => $item->id,
                    'telefone' => $item->telefone,
                    'erro' => $e->getMessage(),
                ]);

                // Aguarda 5 segundos mesmo em caso de erro
                if ($index < $items->count() - 1) {
                    sleep(5);
                }
            }
        }

        // Atualiza o registro principal
        $massMessage->update([
            'status' => 'concluido',
            'enviados' => $enviados,
            'falhas' => $falhas,
            'concluido_em' => now(),
        ]);

        Log::info('Envio em massa concluído', [
            'mass_message_id' => $massMessage->id,
            'enviados' => $enviados,
            'falhas' => $falhas,
        ]);
    }

    /**
     * Tratamento de falha do job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de envio em massa falhou', [
            'mass_message_id' => $this->massMessageId,
            'erro' => $exception->getMessage(),
        ]);

        $massMessage = MassMessage::find($this->massMessageId);
        if ($massMessage) {
            $massMessage->update(['status' => 'erro']);
        }
    }
}
