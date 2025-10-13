<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use RuntimeException;

class SendWhatsappButtonsSample extends Command
{
    protected $signature = 'agenda:testar-botoes {number?}';

    protected $description = 'Envia uma mensagem de botões de exemplo via API Brasil para validar o formato.';

    public function __construct(private WhatsAppService $whatsApp)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $destino = $this->argument('number')
            ?? config('services.whatsapp.test_number')
            ?? config('services.api_brasil.default_test_number');

        if (! $destino) {
            $this->error('Informe um número de destino ou configure WHATSAPP_TEST_NUMBER no .env.');

            return Command::FAILURE;
        }

        $buttons = [
            [
                'url' => 'https://app.apibrasil.io',
                'text' => 'Acessar plataforma',
            ],
            [
                'id' => '3',
                'text' => 'Another text',
            ],
            [
                'code' => '`00020101021226770014BR.GOV.BCB.PIX2555api.itau/pix/qr/v2/cf74fca8-8fe3-4a65-a2ef-c0c2b67823005204000053039865802BR5925JEITTO INSTITUICAO DE PAG6009SAO ULO62070503***630480A1`',
                'text' => 'Copiar Chave PIX',
            ],
            [
                'phoneNumber' => $destino,
                'text' => 'Ligar agora',
            ],
        ];

        $options = [
            'useTemplateButtons' => true,
            'title' => 'Titulo do botao',
            'footer' => 'Fim do botao',
            'delay' => 0,
        ];

        $this->info('Enviando botões de exemplo para ' . $destino . '...');

        try {
            $response = $this->whatsApp->sendButtons($destino, 'Texto do botao', $buttons, $options);
        } catch (RuntimeException $exception) {
            $this->error('Falhou: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $this->info('Resposta da API:');
        $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }
}
