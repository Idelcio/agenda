<?php

namespace App\Jobs;

use App\Models\MediaMessage;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMediaMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hora de timeout

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $mediaMessageId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsApp): void
    {
        $mediaMessage = MediaMessage::find($this->mediaMessageId);

        if (!$mediaMessage) {
            Log::error('MediaMessage não encontrado', ['id' => $this->mediaMessageId]);
            return;
        }

        // ---------------------------------------------------------------
        // 🔐 Carrega empresa dona da mídia e seta credenciais
        // ---------------------------------------------------------------
        $empresa = $mediaMessage->user;

        if (!$empresa || !$empresa->apibrasil_device_token) {
            Log::warning('❌ Empresa sem credenciais de WhatsApp (mídia)', [
                'empresa_id' => $empresa->id ?? null,
                'media_message_id' => $mediaMessage->id,
            ]);
            return;
        }

        $whatsApp->setDeviceCredentials(
            $empresa->apibrasil_device_token,
            $empresa->apibrasil_device_id
        );

        $status = $whatsApp->checkDeviceStatus($empresa->apibrasil_device_token);
        if (!($status['connected'] ?? false)) {
            Log::warning('❌ Sessão da empresa NÃO está conectada para envio de mídia', [
                'empresa_id' => $empresa->id,
                'empresa_nome' => $empresa->name,
                'media_message_id' => $mediaMessage->id,
                'status' => $status,
            ]);
            return;
        }

        Log::info('📡 Enviando MÍDIA usando a empresa correta', [
            'empresa_id' => $empresa->id,
            'empresa_nome' => $empresa->name,
            'media_message_id' => $mediaMessage->id,
        ]);

        Log::info('Iniciando envio em massa de mídia', [
            'media_message_id' => $mediaMessage->id,
            'total_destinatarios' => $mediaMessage->total_destinatarios,
        ]);

        $mediaMessage->update([
            'status' => 'processando',
            'iniciado_em' => now(),
        ]);

        $items = $mediaMessage->items()->where('status', 'pendente')->get();

        $enviados = 0;
        $falhas = 0;

        foreach ($items as $index => $item) {
            try {
                Log::info('Enviando mídia para cliente', [
                    'cliente_id' => $item->cliente_id,
                    'telefone' => $item->telefone,
                    'tipo_arquivo' => $mediaMessage->tipo_arquivo,
                ]);

                // Envia a mídia (imagem ou PDF)
                if ($mediaMessage->tipo_arquivo === 'imagem') {
                    $whatsApp->sendImage(
                        $item->telefone,
                        $mediaMessage->arquivo_path,
                        $mediaMessage->mensagem
                    );
                } else {
                    $whatsApp->sendPdf(
                        $item->telefone,
                        $mediaMessage->arquivo_path,
                        $mediaMessage->mensagem
                    );
                }

                $item->update([
                    'status' => 'enviado',
                    'enviado_em' => now(),
                ]);

                $enviados++;

                Log::info('Mídia enviada com sucesso', [
                    'cliente_id' => $item->cliente_id,
                    'telefone' => $item->telefone,
                ]);

                // Intervalo de 5 segundos entre envios (exceto no último)
                if ($index < $items->count() - 1) {
                    Log::info('Aguardando 5 segundos antes do próximo envio...');
                    sleep(5);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao enviar mídia', [
                    'cliente_id' => $item->cliente_id,
                    'telefone' => $item->telefone,
                    'erro' => $e->getMessage(),
                ]);

                $item->update([
                    'status' => 'erro',
                    'erro_mensagem' => $e->getMessage(),
                ]);

                $falhas++;

                // Aguarda mesmo em caso de erro
                if ($index < $items->count() - 1) {
                    sleep(5);
                }
            }
        }

        $mediaMessage->update([
            'status' => 'concluido',
            'enviados' => $enviados,
            'falhas' => $falhas,
            'concluido_em' => now(),
        ]);

        Log::info('Envio em massa de mídia concluído', [
            'media_message_id' => $mediaMessage->id,
            'enviados' => $enviados,
            'falhas' => $falhas,
        ]);
    }
}
