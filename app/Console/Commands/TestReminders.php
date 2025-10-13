<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;

class TestReminders extends Command
{
    protected $signature = 'agenda:testar-lembretes {--listar : Apenas lista os lembretes pendentes sem enviar}';

    protected $description = 'Testa e visualiza lembretes pendentes (útil para desenvolvimento)';

    public function handle(): int
    {
        $this->info('===== LEMBRETES PENDENTES =====');
        $this->newLine();

        $pendentes = Appointment::dueForReminder()->get();

        if ($pendentes->isEmpty()) {
            $this->warn('Nenhum lembrete pendente para enviar no momento.');
            $this->newLine();
            $this->info('Para testar, crie um compromisso com:');
            $this->info('- notificar_whatsapp = true');
            $this->info('- status_lembrete = pendente');
            $this->info('- lembrar_em <= agora');
            return Command::SUCCESS;
        }

        $this->table(
            ['ID', 'Título', 'Lembrar em', 'Status Lembrete', 'WhatsApp', 'Usuário'],
            $pendentes->map(fn($app) => [
                $app->id,
                $app->titulo,
                $app->lembrar_em?->format('d/m/Y H:i'),
                $app->status_lembrete,
                $app->whatsapp_numero ?? $app->user->whatsapp_number ?? 'N/A',
                $app->user->name,
            ])
        );

        $this->newLine();
        $this->info("Total: {$pendentes->count()} lembrete(s)");

        if ($this->option('listar')) {
            return Command::SUCCESS;
        }

        $this->newLine();
        if ($this->confirm('Deseja enviar estes lembretes agora?', true)) {
            $this->call('agenda:disparar-lembretes');
        }

        return Command::SUCCESS;
    }
}
