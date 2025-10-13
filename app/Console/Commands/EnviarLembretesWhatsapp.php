<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnviarLembretesWhatsapp extends Command
{
    /**
     * Nome e assinatura do comando Artisan.
     */
    protected $signature = 'app:enviar-lembretes-whatsapp';

    /**
     * Descrição do comando.
     */
    protected $description = 'Envia automaticamente os lembretes via WhatsApp para compromissos programados.';

    /**
     * Execução principal.
     */
    public function handle()
    {
        $agora = Carbon::now();
        $inicio = $agora->copy()->subMinutes(5);
        $fim = $agora->copy()->addMinutes(5);

        $this->info('⏰ Verificando lembretes entre ' . $inicio . ' e ' . $fim);

        $lembretes = Appointment::whereBetween('lembrar_em', [$inicio, $fim])
            ->where('notificar_whatsapp', true)
            ->where('status_lembrete', 'pendente')
            ->whereNull('lembrete_enviado_em')
            ->get();

        if ($lembretes->isEmpty()) {
            $this->info('✅ Nenhum lembrete pendente no momento.');
            return Command::SUCCESS;
        }

        foreach ($lembretes as $lembrete) {
            $this->info('📩 Enviando lembrete ID: ' . $lembrete->id . ' — ' . $lembrete->titulo);

            $numero = $lembrete->whatsapp_numero;
            $mensagem = $lembrete->whatsapp_mensagem ?? "Olá! Você tem um agendamento de {$lembrete->titulo} em {$lembrete->inicio->format('d/m/Y à\s H:i')}.";

            if (!$numero) {
                $this->warn("⚠️ Lembrete {$lembrete->id} ignorado — sem número de WhatsApp.");
                continue;
            }

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => env('API_BRASIL_TOKEN'),
                ])->post('https://cluster.apibrasil.io/api/v2/sendText', [
                    'number' => $numero,
                    'text' => $mensagem,
                ]);

                if ($response->successful() && !$response->json('error')) {
                    $lembrete->update([
                        'status_lembrete' => 'enviado',
                        'lembrete_enviado_em' => $agora,
                    ]);

                    $this->info("✅ Lembrete enviado com sucesso para {$numero}");
                    Log::info("✅ Lembrete {$lembrete->id} enviado para {$numero}");
                } else {
                    throw new \Exception($response->body());
                }
            } catch (\Throwable $e) {
                $lembrete->update([
                    'status_lembrete' => 'falhou',
                ]);

                $this->error("❌ Falha ao enviar lembrete {$lembrete->id}: {$e->getMessage()}");
                Log::error("❌ Falha no lembrete {$lembrete->id}: " . $e->getMessage());
            }
        }

        $this->info('🏁 Finalizado o processamento de lembretes.');
        return Command::SUCCESS;
    }
}
