<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use RuntimeException;

class SendWhatsAppTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agenda:whatsapp-teste
                            {numero? : Número de destino no formato +551199999999}
                            {--mensagem= : Mensagem personalizada para envio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia uma mensagem de teste via WhatsApp utilizando a integração configurada.';

    public function __construct(private WhatsAppService $whatsApp)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $destino = $this->argument('numero')
            ?? config('services.whatsapp.test_number');

        if (! $destino) {
            $this->error('Informe um número de destino ou defina WHATSAPP_TEST_NUMBER no .env.');

            return Command::FAILURE;
        }

        $mensagem = $this->option('mensagem') ?: 'Mensagem de teste do Agendoo.';

        try {
            $this->whatsApp->sendMessage($destino, $mensagem);
        } catch (RuntimeException $exception) {
            $this->error('Integração de WhatsApp não configurada: ' . $exception->getMessage());

            return Command::FAILURE;
        } catch (\Throwable $exception) {
            $this->error('Erro ao enviar mensagem: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $this->info("Mensagem enviada para {$destino} com sucesso.");

        return Command::SUCCESS;
    }
}
