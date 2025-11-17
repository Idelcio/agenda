<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendQuickMessageRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Models\MassMessage;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsAppReminderService;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // Separar compromissos por status com todos os registros (não limitado)
        $concluidos = $user->appointments()
            ->with($relations)
            ->where('status', 'concluido')
            ->orderByDesc('inicio')
            ->get();

        $pendentes = $user->appointments()
            ->with($relations)
            ->where('status', 'pendente')
            ->orderByDesc('inicio')
            ->get();

        $cancelados = $user->appointments()
            ->with($relations)
            ->where('status', 'cancelado')
            ->orderByDesc('inicio')
            ->get();

        $recent = $user->appointments()
            ->with($relations)
            ->where('inicio', '<', $now->copy()->startOfDay())
            ->orderByDesc('inicio')
            ->limit(5)
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

        // Busca apenas os clientes cadastrados pela empresa logada
        $usuarios = User::where('user_id', $user->id)
            ->where('tipo', 'cliente')
            ->orderByRaw('LOWER(name)')
            ->get(['id', 'name', 'whatsapp_number']);

        $quickMessageTemplates = $user->quickMessageTemplates()
            ->latest()
            ->take(WhatsAppMessageTemplate::MAX_PER_USER)
            ->get();

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
             'quickMessageTemplates' => $quickMessageTemplates,
             'quickMessageTemplateLimit' => WhatsAppMessageTemplate::MAX_PER_USER,
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
        /** @var User $user */
        $user = auth()->user();
        // Permitir que o usuário edite seus próprios compromissos OU que admins editem qualquer compromisso
        if ($appointment->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Você não tem permissão para editar este compromisso.');
        }

        // Busca apenas os clientes cadastrados pela empresa logada
        $usuarios = User::where('user_id', $user->id)
            ->where('tipo', 'cliente')
            ->orderByRaw('LOWER(name)')
            ->get(['id', 'name', 'whatsapp_number']);

        return view('agenda.edit', [
            'appointment' => $appointment,
            'usuarios' => $usuarios,
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        // Permitir que o usuário atualize seus próprios compromissos OU que admins atualizem qualquer compromisso
        if ($appointment->user_id !== $user->id && !$user->isAdmin()) {
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
        /** @var User $user */
        $user = auth()->user();
        // Permitir que o usuário delete seus próprios compromissos OU que admins deletem qualquer compromisso
        if ($appointment->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Você não tem permissão para excluir este compromisso.');
        }

        $appointment->delete();

        return Redirect::route('agenda.index')->with('status', 'appointment-deleted');
    }

    public function toggleStatus(Appointment $appointment): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        // Permitir que o usuário atualize seus próprios compromissos OU que admins modifiquem qualquer compromisso
        if ($appointment->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Você não tem permissão para modificar este compromisso.');
        }

        $appointment->status = $appointment->isCompleted() ? 'pendente' : 'concluido';
        $appointment->save();

        return Redirect::back()->with('status', 'appointment-status-updated');
    }

    public function updateStatus(Appointment $appointment, string $status): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        // Permitir que qualquer usuário autenticado atualize o status de seus próprios compromissos OU que admins modifiquem qualquer compromisso
        if ($appointment->user_id !== $user->id && !$user->isAdmin()) {
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

        // Adiciona instruções se for um compromisso E tipo for 'compromisso'
        if ($appointment && !$request->hasFile('attachment')) {
            $tipoMensagem = $appointment->tipo_mensagem ?? 'compromisso';
            if ($tipoMensagem === 'compromisso') {
                $mensagem .= "\n\n*Responda:*\n✅ Digite *1* para confirmar\n❌ Digite *2* para cancelar";
            }
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
                    'description' => $appointment->descricao,
                    'type' => 'appointment',
                ];
            });

        $massMessages = $user->massMessages()
            ->with(['items.cliente:id,name'])
            ->whereBetween('scheduled_for', [$startDate, $endDate])
            ->orderBy('scheduled_for')
            ->get()
            ->map(function (MassMessage $massMessage) {
                $start = $massMessage->scheduled_for ?? $massMessage->created_at;
                $end = $start ? $start->copy()->addMinutes(15) : null;
                $destinatarios = $massMessage->items
                    ->pluck('cliente.name')
                    ->filter()
                    ->values();

                return [
                    'id' => 'mass-message-' . $massMessage->id,
                    'title' => $massMessage->titulo ?: 'Mensagem em massa',
                    'start' => $start?->toIso8601String(),
                    'end' => $end?->toIso8601String(),
                    'allDay' => false,
                    'status' => $massMessage->status ?? 'pendente',
                    'whatsapp' => false,
                    'description' => $massMessage->mensagem,
                    'type' => 'mass_message',
                    'total_destinatarios' => $massMessage->total_destinatarios,
                    'destinatarios_nomes' => $destinatarios,
                ];
            });

        $allEvents = $events
            ->values()
            ->concat(collect($massMessages)->values())
            ->values();

        return response()->json($allEvents);
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

    public function gerarPdfSemanal(Request $request)
    {
        $user = $request->user();
        $hoje = now();
        $periodo = $request->query('periodo', 'semana'); // dia, semana, mes ou personalizado
        $mesOffset = $request->query('mes_offset', 0); // Para mês anterior/próximo

        // Define início e fim baseado no período
        switch ($periodo) {
            case 'dia':
                $inicio = $hoje->copy()->startOfDay();
                $fim = $hoje->copy()->endOfDay();
                $tituloPeriodo = 'Diária';
                $periodoTexto = $inicio->format('d/m/Y');
                $nomeArquivo = 'agenda-diaria-' . $inicio->format('d-m-Y') . '.pdf';
                break;

            case 'mes':
                // Aplica offset de meses (ex: -1 para mês anterior, +1 para próximo)
                $dataBase = $hoje->copy()->addMonths($mesOffset);
                $inicio = $dataBase->copy()->startOfMonth();
                $fim = $dataBase->copy()->endOfMonth();
                $tituloPeriodo = 'Mensal';
                $periodoTexto = $inicio->format('F/Y');
                $nomeArquivo = 'agenda-mensal-' . $inicio->format('m-Y') . '.pdf';
                break;

            case 'personalizado':
                // Período personalizado com datas específicas
                $dataInicio = $request->query('data_inicio');
                $dataFim = $request->query('data_fim');

                if ($dataInicio && $dataFim) {
                    $inicio = \Carbon\Carbon::parse($dataInicio)->startOfDay();
                    $fim = \Carbon\Carbon::parse($dataFim)->endOfDay();
                    $tituloPeriodo = 'Personalizada';
                    $periodoTexto = $inicio->format('d/m/Y') . ' - ' . $fim->format('d/m/Y');
                    $nomeArquivo = 'agenda-' . $inicio->format('d-m-Y') . '-a-' . $fim->format('d-m-Y') . '.pdf';
                } else {
                    // Fallback para semana atual se não informar datas
                    $inicio = $hoje->copy()->startOfWeek();
                    $fim = $hoje->copy()->endOfWeek();
                    $tituloPeriodo = 'Semanal';
                    $periodoTexto = $inicio->format('d/m/Y') . ' - ' . $fim->format('d/m/Y');
                    $nomeArquivo = 'agenda-semanal-' . $inicio->format('d-m-Y') . '.pdf';
                }
                break;

            case 'semana':
            default:
                $inicio = $hoje->copy()->startOfWeek();
                $fim = $hoje->copy()->endOfWeek();
                $tituloPeriodo = 'Semanal';
                $periodoTexto = $inicio->format('d/m/Y') . ' - ' . $fim->format('d/m/Y');
                $nomeArquivo = 'agenda-semanal-' . $inicio->format('d-m-Y') . '.pdf';
                break;
        }

        // Busca compromissos do período
        $compromissos = $user->appointments()
            ->with(['destinatario:id,name,whatsapp_number'])
            ->whereBetween('inicio', [$inicio, $fim])
            ->orderBy('inicio')
            ->get();

        // Agrupa por dia
        $compromissosPorDia = $compromissos->groupBy(function ($compromisso) {
            return $compromisso->inicio->timezone(config('app.timezone'))->format('Y-m-d');
        });

        // Dados para o PDF
        $dados = [
            'empresa' => $user->name,
            'periodo' => $periodoTexto,
            'tituloPeriodo' => $tituloPeriodo,
            'compromissosPorDia' => $compromissosPorDia,
            'inicioSemana' => $inicio,
            'fimSemana' => $fim,
        ];

        // Gera o PDF
        $pdf = Pdf::loadView('agenda.pdf.semanal', $dados);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($nomeArquivo);
    }
}
