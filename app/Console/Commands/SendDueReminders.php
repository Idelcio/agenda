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
            $total = 0;

            Appointment::dueForReminder()->chunkById(50, function ($appointments) use (&$total) {
                /** @var Appointment $appointment */
                foreach ($appointments as $appointment) {
                    try {
                        $this->reminderService->sendAppointmentReminder($appointment);
                        $total++;
                    } catch (RuntimeException $exception) {
                        Log::warning('Lembrete nao enviado', [
                            'appointment_id' => $appointment->id,
                            'exception' => $exception->getMessage(),
                        ]);
                    } catch (\Throwable $exception) {
                        Log::error('Falha ao enviar lembrete automatico.', [
                            'appointment_id' => $appointment->id,
                            'exception' => $exception->getMessage(),
                        ]);
                    }
                }
            });

            $this->info("Lembretes enviados: {$total}");

            return Command::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->error('Integracao de WhatsApp nao configurada. Configure API_BRASIL_* no .env.');

            return Command::FAILURE;
        }
    }
}
