<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IniciarWhatsApp extends Command
{
    protected $signature = 'agenda:whatsapp-iniciar';
    protected $description = 'Inicia uma nova sessão do WhatsApp e exibe o QR Code para autenticação';

    public function __construct(private WhatsAppService $whatsapp)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🚀 Iniciando nova sessão do WhatsApp...');
        $this->newLine();

        try {
            // Tenta iniciar a sessão
            $response = $this->whatsapp->startSession(
                deviceName: config('app.name', 'Agenda Digital'),
                number: null,
                autoCloseMs: null
            );

            $this->info('✅ Sessão iniciada com sucesso!');
            $this->newLine();

            // Exibe informações da resposta
            if (isset($response['status']) && $response['status'] === 'CONNECTED') {
                $this->components->success('WhatsApp já está conectado!');
                return Command::SUCCESS;
            }

            // Se precisa escanear QR Code
            if (isset($response['qrcode'])) {
                $this->warn('📱 Escaneie o QR Code abaixo com seu WhatsApp:');
                $this->newLine();
                $this->line($response['qrcode']);
                $this->newLine();
            }

            // Exibe o device_token se disponível
            if (isset($response['device_token'])) {
                $this->info('🔑 Device Token: ' . $response['device_token']);
                $this->newLine();
                $this->warn('⚠️  Adicione este token ao seu arquivo .env:');
                $this->line('API_BRASIL_DEVICE_TOKEN=' . $response['device_token']);
                $this->newLine();
            }

            // Exibe outras informações úteis
            if (isset($response['device_id'])) {
                $this->info('📱 Device ID: ' . $response['device_id']);
            }

            $this->newLine();
            $this->components->info('Após escanear o QR Code, a sessão estará ativa e você poderá enviar mensagens.');

            Log::info('✅ Nova sessão WhatsApp iniciada via comando', ['response' => $response]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->components->error('❌ Erro ao iniciar sessão: ' . $e->getMessage());

            Log::error('❌ Erro ao iniciar sessão WhatsApp', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
