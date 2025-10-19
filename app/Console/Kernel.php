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
            ->appendOutputTo(storage_path('logs/schedule.log'));

        // 🔄 Sincroniza respostas do WhatsApp a cada 5 minutos
        $schedule->command('agenda:sincronizar-respostas')
            ->everyFiveMinutes()
            ->appendOutputTo(storage_path('logs/schedule.log'));
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
