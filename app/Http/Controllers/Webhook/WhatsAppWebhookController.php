<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ChatbotMessage;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppWebhookController extends Controller
{
    public function __invoke(Request $request, WhatsAppService $whatsApp)
    {
        if (! $this->isAuthorized($request)) {
            return response('Não autorizado', Response::HTTP_UNAUTHORIZED);
        }

        $from = $this->normalizeNumber($request->input('From') ?? $request->input('from'));
        $body = trim((string) ($request->input('Body') ?? $request->input('message') ?? $request->input('text') ?? ''));
        $user = $from ? User::where('whatsapp_number', $from)->first() : null;

        ChatbotMessage::create([
            'user_id' => $user?->id,
            'whatsapp_numero' => $from,
            'direcao' => 'entrada',
            'conteudo' => $body,
            'payload' => $request->all(),
        ]);

        if (! $from || $body === '') {
            return response('OK', Response::HTTP_OK);
        }

        if (! $user) {
            $message = 'Olá! Não encontramos sua conta. Acesse o portal da Agenda Digital, cadastre-se e informe este número na página de perfil.';
            $this->sendReply($whatsApp, $from, $message, null, ['reason' => 'user-not-found']);

            return response('OK', Response::HTTP_OK);
        }

        [$reply, $meta] = $this->handleCommand($user, $body);

        $this->sendReply($whatsApp, $from, $reply, $user, $meta);

        return response('OK', Response::HTTP_OK);
    }

    private function handleCommand(User $user, string $body): array
    {
        $normalized = Str::upper(Str::of($body)->trim());

        if ($normalized === 'MENU' || $normalized === 'AJUDA') {
            return [$this->menuMessage(), ['command' => 'menu']];
        }

        if ($normalized === 'LISTAR') {
            return [$this->listAppointmentsMessage($user), ['command' => 'listar']];
        }

        if (Str::startsWith($normalized, 'CRIAR')) {
            return $this->createAppointmentFromCommand($user, $body);
        }

        return [$this->unknownCommandMessage(), ['command' => 'unknown']];
    }

    private function createAppointmentFromCommand(User $user, string $body): array
    {
        $payload = trim(Str::after($body, ' '));
        $parts = array_map('trim', array_filter(explode(';', $payload)));

        if (count($parts) < 2) {
            return ['Formato inválido. Utilize: CRIAR Título; 25/12/2025; 14:00', ['command' => 'criar', 'status' => 'invalid-format']];
        }

        [$titulo, $data, $hora] = [$parts[0], $parts[1], $parts[2] ?? '09:00'];

        try {
            $inicio = Carbon::createFromFormat('d/m/Y H:i', "{$data} {$hora}", config('app.timezone'));
        } catch (\Throwable $exception) {
            return ['Não foi possível interpretar a data/hora. Use o formato 25/12/2025; 14:00', ['command' => 'criar', 'status' => 'invalid-date']];
        }

        $appointment = new Appointment([
            'titulo' => $titulo,
            'inicio' => $inicio,
            'dia_inteiro' => false,
            'descricao' => 'Criado via chatbot do WhatsApp.',
            'status' => 'pendente',
            'notificar_whatsapp' => false,
        ]);

        $appointment->user()->associate($user);
        $appointment->save();

        return [
            "Compromisso \"{$titulo}\" criado para {$inicio->format('d/m/Y H:i')}.",
            ['command' => 'criar', 'status' => 'created', 'appointment_id' => $appointment->id],
        ];
    }

    private function listAppointmentsMessage(User $user): string
    {
        $appointments = $user->appointments()
            ->where('inicio', '>=', now()->startOfDay())
            ->orderBy('inicio')
            ->limit(5)
            ->get();

        if ($appointments->isEmpty()) {
            return 'Você não possui compromissos futuros.';
        }

        $lines = $appointments->map(function (Appointment $appointment) {
            $data = $appointment->inicio->timezone(config('app.timezone'))->format('d/m H:i');
            $status = $appointment->isCompleted() ? '[ok]' : '[pendente]';

            return "{$status} {$data} - {$appointment->titulo}";
        });

        return "Próximos compromissos:\n" . $lines->implode("\n");
    }

    private function menuMessage(): string
    {
        return <<<TXT
Comandos disponíveis:
- MENU ou AJUDA: exibe esta mensagem.
- LISTAR: mostra os próximos compromissos.
- CRIAR Título; 25/12/2025; 14:00

Os horários seguem o fuso de São Paulo.
TXT;
    }

    private function unknownCommandMessage(): string
    {
        return 'Não entendi o comando. Envie MENU para ver as opções.';
    }

    private function sendReply(WhatsAppService $service, string $to, string $message, ?User $user = null, ?array $meta = null): void
    {
        try {
            $service->sendMessage($to, $message);
        } catch (RuntimeException $exception) {
            Log::warning('WhatsApp service not configured', ['exception' => $exception->getMessage()]);

            return;
        } catch (\Throwable $exception) {
            Log::error('Erro ao enviar mensagem de WhatsApp', ['exception' => $exception]);

            return;
        }

        ChatbotMessage::create([
            'user_id' => $user?->id,
            'whatsapp_numero' => $to,
            'direcao' => 'saida',
            'conteudo' => $message,
            'payload' => $meta,
        ]);
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = config('services.whatsapp.webhook_secret');

        if (! $secret) {
            return true;
        }

        $provided = $request->header('X-Webhook-Secret', $request->input('secret'));

        return hash_equals($secret, (string) $provided);
    }

    private function normalizeNumber(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $clean = Str::of($value)->replace('whatsapp:', '')->trim();

        return $clean === '' ? null : (string) $clean;
    }
}