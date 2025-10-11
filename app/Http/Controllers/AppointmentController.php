<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendQuickMessageRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\WhatsAppReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use RuntimeException;

class AppointmentController extends Controller
{
    private const DEFAULT_REMINDER_MINUTES = 30;

    public function __construct(private WhatsAppReminderService $reminderService)
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $upcoming = $user->appointments()
            ->where('inicio', '>=', now()->startOfDay())
            ->orderBy('inicio')
            ->get();

        $recent = $user->appointments()
            ->where('inicio', '<', now()->startOfDay())
            ->orderByDesc('inicio')
            ->limit(10)
            ->get();

        $stats = [
            'total' => $user->appointments()->count(),
            'pendentes' => $user->appointments()->where('status', 'pendente')->count(),
            'confirmados' => $user->appointments()->where('status', 'confirmado')->count(),
            'cancelados' => $user->appointments()->where('status', 'cancelado')->count(),
            'concluidos' => $user->appointments()->where('status', 'concluido')->count(),
            'com_lembrete' => $user->appointments()->where('notificar_whatsapp', true)->count(),
        ];

        return view('agenda.index', [
            'upcoming' => $upcoming,
            'recent' => $recent,
            'stats' => $stats,
            'defaultWhatsapp' => $user->whatsapp_number,
            'quickMessageDefaults' => [
                'destinatario' => $user->whatsapp_number ?? config('services.whatsapp.test_number'),
            ],
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        return Redirect::route('agenda.index');
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (empty($data['whatsapp_numero'])) {
            $data['whatsapp_numero'] = $user->whatsapp_number;
        }

        if (! ($data['dia_inteiro'] ?? false) && empty($data['fim'])) {
            $data['fim'] = null;
        }

        if (($data['dia_inteiro'] ?? false) === true) {
            $data['fim'] = null;
        }

        $appointment = $user->appointments()->create($data);

        $this->syncReminder($appointment, $data['antecedencia_minutos'] ?? null);
        $appointment->save();

        return Redirect::route('agenda.index')->with('status', 'appointment-created');
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorize('update', $appointment);

        return view('agenda.edit', [
            'appointment' => $appointment,
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validated();

        if (empty($data['whatsapp_numero'])) {
            $data['whatsapp_numero'] = $appointment->user->whatsapp_number;
        }

        if (($data['dia_inteiro'] ?? false) === true) {
            $data['fim'] = null;
        }

        $appointment->fill($data);
        $this->syncReminder($appointment, $data['antecedencia_minutos'] ?? null);
        $appointment->save();

        return Redirect::route('agenda.index')->with('status', 'appointment-updated');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return Redirect::route('agenda.index')->with('status', 'appointment-deleted');
    }

    public function toggleStatus(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $appointment->status = $appointment->isCompleted() ? 'pendente' : 'concluido';
        $appointment->save();

        return Redirect::back()->with('status', 'appointment-status-updated');
    }

    public function sendReminder(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        try {
            $this->reminderService->sendAppointmentReminder($appointment);
        } catch (RuntimeException $exception) {
            return Redirect::back()->withErrors([
                'whatsapp' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return Redirect::back()->withErrors([
                'whatsapp' => 'Nao foi possivel enviar o lembrete: ' . $exception->getMessage(),
            ]);
        }

        return Redirect::back()->with('status', 'appointment-reminder-sent');
    }

    public function sendQuickMessage(SendQuickMessageRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $destino = $dados['destinatario'];
        $mensagem = $dados['mensagem'] ?? '';

        try {
            $this->reminderService->sendQuickMessage(
                null,
                $destino,
                $mensagem,
                $request->file('attachment'),
                auth()->id()
            );
        } catch (RuntimeException $exception) {
            return Redirect::back()->withErrors([
                'quick_whatsapp' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return Redirect::back()->withErrors([
                'quick_whatsapp' => 'Nao foi possivel enviar a mensagem: ' . $exception->getMessage(),
            ]);
        }

        return Redirect::back()->with('status', 'quick-message-sent');
    }

    public function events(Request $request): JsonResponse
    {
        $user = $request->user();
        $start = $request->query('start');
        $end = $request->query('end');

        try {
            $startDate = $start ? Carbon::parse($start)->startOfDay() : now()->startOfMonth();
        } catch (\Throwable $exception) {
            $startDate = now()->startOfMonth();
        }

        try {
            $endDate = $end ? Carbon::parse($end)->endOfDay() : now()->endOfMonth();
        } catch (\Throwable $exception) {
            $endDate = now()->endOfMonth();
        }

        $events = $user->appointments()
            ->whereBetween('inicio', [$startDate, $endDate])
            ->orderBy('inicio')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->titulo,
                    'start' => $appointment->inicio?->toIso8601String(),
                    'end' => $appointment->fim?->toIso8601String(),
                    'allDay' => $appointment->dia_inteiro,
                    'status' => $appointment->status,
                    'whatsapp' => $appointment->notificar_whatsapp,
                ];
            });

        return response()->json($events);
    }

    private function syncReminder(Appointment $appointment, ?int $antecedenciaMinutos): void
    {
        if (! $appointment->notificar_whatsapp) {
            $appointment->lembrar_em = null;
            $appointment->antecedencia_minutos = null;
            return;
        }

        $intervalo = $antecedenciaMinutos ?? $appointment->antecedencia_minutos ?? self::DEFAULT_REMINDER_MINUTES;

        $appointment->computeReminderTime($intervalo);
        $appointment->lembrete_enviado_em = null;

        if (! $appointment->whatsapp_mensagem) {
            $appointment->whatsapp_mensagem = $this->buildDefaultReminderMessage($appointment);
        }
    }

    private function buildDefaultReminderMessage(Appointment $appointment): string
    {
        $inicio = $appointment->inicio?->timezone(config('app.timezone'))->format('d/m/Y H:i');

        return "Lembrete: {$appointment->titulo} em {$inicio}.";
    }
}
