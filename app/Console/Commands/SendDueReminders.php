<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\WhatsAppReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendDueReminders extends Command
{
    protected $signature = 'agenda:disparar-lembretes';

    protected $description = 'Envia lembretes pendentes de compromissos via WhatsApp.';

    public function __construct(private WhatsAppReminderService $reminderService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $totalEnviados = 0;
            $totalFalhas = 0;

            Appointment::dueForReminder()->chunkById(50, function ($appointments) use (&$totalEnviados, &$totalFalhas) {
                /** @var Appointment $appointment */
                foreach ($appointments as $appointment) {
                    try {
                        $this->reminderService->sendAppointmentReminder($appointment);
                        $totalEnviados++;
                        $this->info("✓ Lembrete enviado: {$appointment->titulo} (ID: {$appointment->id})");
                    } catch (RuntimeException $exception) {
                        $appointment->markReminderAsFailed();
                        $totalFalhas++;
                        Log::warning('Lembrete nao enviado', [
                            'appointment_id' => $appointment->id,
                            'exception' => $exception->getMessage(),
                        ]);
                        $this->warn("✗ Falha: {$appointment->titulo} - {$exception->getMessage()}");
                    } catch (\Throwable $exception) {
                        $appointment->markReminderAsFailed();
                        $totalFalhas++;
                        Log::error('Falha ao enviar lembrete automatico.', [
                            'appointment_id' => $appointment->id,
                            'exception' => $exception->getMessage(),
                        ]);
                        $this->error("✗ Erro: {$appointment->titulo} - {$exception->getMessage()}");
                    }
                }
            });

            $this->newLine();
            $this->info("===== RESUMO =====");
            $this->info("Lembretes enviados: {$totalEnviados}");
            if ($totalFalhas > 0) {
                $this->warn("Falhas: {$totalFalhas}");
            }

            return Command::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->error('Integracao de WhatsApp nao configurada. Configure API_BRASIL_* no .env.');

            return Command::FAILURE;
        }
    }
}
