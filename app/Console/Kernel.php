<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 🕐 Envia lembretes automaticamente a cada minuto
        $schedule->command('agenda:disparar-lembretes')
            ->everyMinute()
            ->withoutOverlapping(2)
            ->appendOutputTo(storage_path('logs/disparar.log'));

        // 🔄 Sincroniza respostas do WhatsApp a cada minuto
        $schedule->command('agenda:sincronizar-respostas')
            ->everyMinute()
            ->withoutOverlapping(2)
            ->appendOutputTo(storage_path('logs/sincronizar.log'));

        // 🔁 Gera compromissos recorrentes uma vez por dia (às 6h da manhã)
        $schedule->command('agenda:gerar-recorrentes')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/recorrentes.log'));

        // 📨 Processa mensagens agendadas a cada minuto
        $schedule->command('messages:process-scheduled')
            ->everyMinute()
            ->withoutOverlapping(2)
            ->appendOutputTo(storage_path('logs/scheduled-messages.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
