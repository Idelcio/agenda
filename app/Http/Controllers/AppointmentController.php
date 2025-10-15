<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendQuickMessageRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Services\WhatsAppReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class AppointmentController extends Controller
{
    private const DEFAULT_REMINDER_MINUTES = 30;

    public function __construct(private WhatsAppReminderService $reminderService)
    {
        $this->middleware(['auth', 'verified']);
    }

    public function lembretesPendentes(): JsonResponse
    {
        $user = Auth::user();
        $agora = now();
        $inicio = $agora->copy()->subMinutes(5);
        $fim = $agora->copy()->addMinutes(5);

        $lembretes = Appointment::where('user_id', $user->id)
            ->whereBetween('lembrar_em', [$inicio, $fim])
            ->where('notificar_whatsapp', true)
            ->where('status_lembrete', 'pendente')
            ->whereNull('lembrete_enviado_em')
            ->orderBy('lembrar_em')
            ->get([
                'id',
                'titulo',
                'whatsapp_numero',
                'whatsapp_mensagem',
                'lembrar_em',
            ]);

        return response()->json($lembretes);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $now = now();
        $relations = [
            'destinatario:id,name,whatsapp_number',
            'user:id,name,whatsapp_number',
        ];

        $dueReminderIds = $user->appointments()
            ->where('notificar_whatsapp', true)
            ->where('status_lembrete', 'pendente')
            ->whereNotNull('lembrar_em')
            ->where('lembrar_em', '<=', $now)
            ->pluck('id');

        $dueReminders = $dueReminderIds->isEmpty()
            ? collect()
            : $user->appointments()
                ->with($relations)
                ->whereIn('id', $dueReminderIds)
                ->orderBy('lembrar_em')
                ->get();

        $upcoming = $user->appointments()
            ->with($relations)
            ->where('inicio', '>=', $now->copy()->startOfDay())
            ->when($dueReminderIds->isNotEmpty(), fn($query) => $query->whereNotIn('id', $dueReminderIds))
            ->orderBy('inicio')
            ->get();

        // Separar compromissos por status
        $concluidos = $user->appointments()
            ->with($relations)
            ->where('status', 'concluido')
            ->orderByDesc('inicio')
            ->limit(20)
            ->get();

        $pendentes = $user->appointments()
            ->with($relations)
            ->where('status', 'pendente')
            ->orderByDesc('inicio')
            ->limit(20)
            ->get();

        $cancelados = $user->appointments()
            ->with($relations)
            ->where('status', 'cancelado')
            ->orderByDesc('inicio')
            ->limit(20)
            ->get();

        $recent = $user->appointments()
            ->with($relations)
            ->where('inicio', '<', $now->copy()->startOfDay())
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
            'lembretes_enviados' => $user->appointments()->where('status_lembrete', 'enviado')->count(),
            'lembretes_pendentes' => $user->appointments()->where('status_lembrete', 'pendente')->where('notificar_whatsapp', true)->count(),
            'lembretes_falharam' => $user->appointments()->where('status_lembrete', 'falhou')->count(),
        ];

        // Busca todos os usuários para seleção de destinatário
        $usuarios = User::orderBy('name')->get(['id', 'name', 'whatsapp_number']);

        return view('agenda.index', [
            'upcoming' => $upcoming,
            'recent' => $recent,
            'concluidos' => $concluidos,
            'pendentes' => $pendentes,
            'cancelados' => $cancelados,
            'stats' => $stats,
            'dueReminders' => $dueReminders,
            'defaultWhatsapp' => $user->whatsapp_number,
            'usuarios' => $usuarios,
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

        if (! array_key_exists('notificar_whatsapp', $data)) {
            $data['notificar_whatsapp'] = true;
        }

        // Se selecionou destinatário, usa o número dele
        if (!empty($data['destinatario_user_id'])) {
            $destinatario = User::find($data['destinatario_user_id']);
            if ($destinatario && $destinatario->whatsapp_number) {
                $data['whatsapp_numero'] = $destinatario->whatsapp_number;
            }
        }

        // Se não tem número, usa o do usuário logado
        if (empty($data['whatsapp_numero'])) {
            $data['whatsapp_numero'] = $user->whatsapp_number;
        }

        // Normaliza o número de WhatsApp removendo caracteres especiais e garantindo +55
        if (!empty($data['whatsapp_numero'])) {
            $numero = preg_replace('/\D+/', '', $data['whatsapp_numero']);
            // Se não começar com 55, adiciona
            if (!str_starts_with($numero, '55')) {
                $numero = '55' . $numero;
            }
            $data['whatsapp_numero'] = $numero;
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
        // Permitir que o usuário edite seus próprios compromissos
        if ($appointment->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para editar este compromisso.');
        }

        return view('agenda.edit', [
            'appointment' => $appointment,
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        // Permitir que o usuário atualize seus próprios compromissos
        if ($appointment->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para atualizar este compromisso.');
        }

        $data = $request->validated();

        if (! array_key_exists('notificar_whatsapp', $data)) {
            $data['notificar_whatsapp'] = true;
        }

        if (empty($data['whatsapp_numero'])) {
            $data['whatsapp_numero'] = $appointment->user->whatsapp_number;
        }

        // Normaliza o número de WhatsApp removendo caracteres especiais e garantindo +55
        if (!empty($data['whatsapp_numero'])) {
            $numero = preg_replace('/\D+/', '', $data['whatsapp_numero']);
            // Se não começar com 55, adiciona
            if (!str_starts_with($numero, '55')) {
                $numero = '55' . $numero;
            }
            $data['whatsapp_numero'] = $numero;
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
        // Permitir que o usuário delete seus próprios compromissos
        if ($appointment->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para excluir este compromisso.');
        }

        $appointment->delete();

        return Redirect::route('agenda.index')->with('status', 'appointment-deleted');
    }

    public function toggleStatus(Appointment $appointment): RedirectResponse
    {
        // Permitir que o usuário atualize seus próprios compromissos
        if ($appointment->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para modificar este compromisso.');
        }

        $appointment->status = $appointment->isCompleted() ? 'pendente' : 'concluido';
        $appointment->save();

        return Redirect::back()->with('status', 'appointment-status-updated');
    }

    public function updateStatus(Appointment $appointment, string $status): RedirectResponse
    {
        // Permitir que qualquer usuário autenticado atualize o status de seus próprios compromissos
        if ($appointment->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para modificar este compromisso.');
        }

        // Validar status permitidos
        $allowedStatuses = ['pendente', 'confirmado', 'concluido', 'cancelado'];

        if (!in_array($status, $allowedStatuses)) {
            return Redirect::back()->withErrors(['status' => 'Status inválido.']);
        }

        $appointment->status = $status;
        $appointment->save();

        return Redirect::back()->with('status', 'appointment-status-updated');
    }

    public function sendReminder(Appointment $appointment): RedirectResponse
    {
        $this->authorize('remind', $appointment);

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

        return Redirect::back()
            ->with('status', 'appointment-reminder-sent')
            ->with('reminder_sent', $appointment->id);
    }

    public function sendQuickMessage(SendQuickMessageRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $destino = $dados['destinatario'];
        $mensagem = $dados['mensagem'] ?? '';
        $appointmentId = $request->input('appointment_id');

        // Se há appointment_id, busca o compromisso
        $appointment = $appointmentId ? Appointment::find($appointmentId) : null;

        // Adiciona instruções se for um compromisso
        if ($appointment && !$request->hasFile('attachment')) {
            $mensagem .= "\n\n*Responda:*\n✅ Digite *1* para confirmar\n❌ Digite *2* para cancelar";
        }

        try {
            $messageRecord = $this->reminderService->sendQuickMessage(
                $appointment,
                $destino,
                $mensagem,
                $request->file('attachment'),
                auth()->id(),
                withConfirmationButtons: false
            );

            // Se for um lembrete de compromisso, marca como enviado
            if ($appointment && $appointment->notificar_whatsapp) {
                $appointment->markAsReminded();
            }
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

        return Redirect::back()
            ->with('status', 'quick-message-sent')
            ->with('quick_message_sent', $messageRecord->phone ?? $destino)
            ->with('reminder_sent', $appointment?->id);
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
            $appointment->status_lembrete = null;
            $appointment->lembrete_enviado_em = null;
            return;
        }

        $intervalo = $antecedenciaMinutos ?? $appointment->antecedencia_minutos ?? self::DEFAULT_REMINDER_MINUTES;

        $appointment->computeReminderTime($intervalo);
        $appointment->status_lembrete = 'pendente';
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
