<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncWhatsappReplies extends Command
{
    protected $signature = 'agenda:sincronizar-respostas';
    protected $description = 'Busca novas mensagens no WhatsApp e atualiza a agenda conforme respostas dos clientes.';

    private const LOCK_KEY = 'whatsapp-sync-lock';
    private const LOCK_TTL = 90; // 90 segundos (3x o intervalo de 30s)

    public function __construct(private WhatsAppService $whatsApp)
    {
        parent::__construct();
    }

    public function handle()
    {
        // 🔒 Verifica se já existe outra instância rodando
        if (Cache::has(self::LOCK_KEY)) {
            $this->error('❌ Outra instância de sincronização já está rodando!');
            $this->error('   Se você tem certeza que não há outra instância, execute:');
            $this->error('   php artisan cache:forget ' . self::LOCK_KEY);
            return self::FAILURE;
        }

        // 🔐 Cria o lock inicial
        Cache::put(self::LOCK_KEY, getmypid(), self::LOCK_TTL);

        $this->info('🔄 Iniciando sincronização contínua das respostas do WhatsApp...');
        $this->info('🔒 Lock adquirido com sucesso (PID: ' . getmypid() . ')');

        try {
            while (true) {
                try {
                    // 🔄 Renova o lock a cada ciclo
                    Cache::put(self::LOCK_KEY, getmypid(), self::LOCK_TTL);

                    // 🔹 Busca todas as empresas com setup completo
                    $empresas = \App\Models\User::where('tipo', 'empresa')
                        ->where('apibrasil_setup_completed', true)
                        ->whereNotNull('apibrasil_device_token')
                        ->get();

                    if ($empresas->isEmpty()) {
                        $this->warn('⚠️ Nenhuma empresa com WhatsApp configurado encontrada.');
                        Log::warning('Nenhuma empresa com WhatsApp configurado');
                    }

                    // 🔹 Processa mensagens de cada empresa
                    foreach ($empresas as $empresa) {
                        $this->info("📱 Verificando mensagens da empresa: {$empresa->name} (ID: {$empresa->id})");

                        // Configura as credenciais da empresa atual
                        $this->whatsApp->useUserCredentials($empresa);

                        try {
                            $this->whatsApp->fetchNewMessagesAndProcess();
                        } catch (\RuntimeException $exception) {
                            $this->error("❌ Erro ao consultar mensagens da empresa {$empresa->name}: " . $exception->getMessage());
                            Log::error('❌ Erro ao consultar mensagens do WhatsApp', [
                                'empresa_id' => $empresa->id,
                                'empresa_nome' => $empresa->name,
                                'erro' => $exception->getMessage(),
                                'trace' => $exception->getTraceAsString(),
                            ]);
                        }
                    }

                    $this->info('✅ Ciclo de verificação concluído: ' . now());
                } catch (\Throwable $t) {
                    Log::error('💥 Erro inesperado no loop de sincronização WhatsApp', [
                        'erro' => $t->getMessage(),
                        'trace' => $t->getTraceAsString(),
                    ]);
                }

                // ⏱️ Aguarda 30 segundos antes de repetir o processo
                sleep(30);
            }
        } finally {
            // 🔓 Remove o lock quando o processo terminar (Ctrl+C, erro fatal, etc)
            Cache::forget(self::LOCK_KEY);
            $this->info('🔓 Lock liberado.');
        }
    }
}
