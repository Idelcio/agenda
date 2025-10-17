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

        // 🔹 Tipo de evento vindo da API Brasil
        $eventType = data_get($request, 'data.wook'); // Ex: "RECEIVE_MESSAGE", "MESSAGE_STATUS"
        $type = data_get($request, 'data.data.type'); // Ex: "text", "image", etc.

        // 🔹 Ignora eventos que não sejam mensagens recebidas
        if ($eventType !== 'RECEIVE_MESSAGE') {
            Log::info("Webhook ignorado (evento: {$eventType})");
            return response('OK', Response::HTTP_OK);
        }

        // 🔹 Captura número e conteúdo corretamente
        $from = $this->normalizeNumber(data_get($request, 'data.from'));

        // ✅ Corrigido: pega exatamente o body certo ("2" no teu exemplo)
        $body = trim((string)(
            data_get($request, 'data.data.body') ??
            data_get($request, 'data.body') ??
            data_get($request, 'data.content') ??
            data_get($request, 'body') ??
            ''
        ));

        // 🔹 Pega legenda caso seja mídia
        $caption = data_get($request, 'data.data.caption');
        $body = $body ?: $caption;

        Log::info('📩 Webhook recebido', [
            'from' => $from,
            'body' => $body,
            'raw' => $request->all(),
        ]);

        // 🔹 Localiza o usuário pelo número do WhatsApp (principal ou cliente vinculado)
        $user = $from ? User::where('whatsapp_number', $from)->first() : null;

        // 🔹 Se não encontrou, verifica se é um cliente vinculado a alguma empresa
        if (!$user && $from) {
            $user = User::whereHas('clientes', function ($query) use ($from) {
                $query->where('whatsapp_number', $from);
            })->first();

            // Se encontrou a empresa, pega o cliente real
            if ($user) {
                $user = $user->clientes()->where('whatsapp_number', $from)->first();
            }
        }

        // 🔹 Registra a mensagem recebida
        ChatbotMessage::create([
            'user_id' => $user?->id,
            'whatsapp_numero' => $from,
            'direcao' => 'entrada',
            'conteudo' => $body,
            'payload' => $request->all(),
        ]);

        // 🔹 Caso não tenha corpo de mensagem ou número
        if (! $from || $body === '') {
            return response('OK', Response::HTTP_OK);
        }

        // 🔹 Caso o número não pertença a nenhum usuário
        if (! $user) {
            // Nenhuma mensagem será enviada
            return response('OK', Response::HTTP_OK);
        }

        // 🔹 Processa o comando (1, 2, MENU, etc.)
        [$reply, $meta] = $this->handleCommand($user, $from, $body);

        // 🔹 Responde o usuário
        $this->sendReply($whatsApp, $from, $reply, $user, $meta);

        return response('OK', Response::HTTP_OK);
    }



    private function handleCommand(User $user, string $whatsappNumber, string $body): array
    {
        $normalized = Str::upper(Str::of($body)->trim());

        // 🔹 Normaliza o número removendo caracteres especiais
        $cleanNumber = preg_replace('/\D+/', '', $whatsappNumber);

        Log::info('🔍 Buscando compromisso', [
            'whatsapp_original' => $whatsappNumber,
            'whatsapp_limpo' => $cleanNumber,
            'comando' => $normalized,
        ]);

        // 🔹 Localiza o compromisso mais recente com lembrete enviado para este número de WhatsApp
        // Não filtra por status para sempre pegar o lembrete mais recente enviado
        $appointment = Appointment::query()
            ->where('whatsapp_numero', $cleanNumber)
            ->where('status_lembrete', 'enviado')
            ->latest('lembrete_enviado_em')
            ->first();

        Log::info('📋 Resultado da busca', [
            'encontrado' => $appointment ? 'sim' : 'não',
            'appointment_id' => $appointment?->id,
            'titulo' => $appointment?->titulo,
            'status_atual' => $appointment?->status,
        ]);

        // 🔹 Cliente respondeu "1" → marcar como CONFIRMADO
        if (in_array($normalized, ['1', 'CONFIRMAR', 'SIM', 'OK'])) {
            if ($appointment) {
                $appointment->update(['status' => 'confirmado']);

                Log::info('✅ Compromisso confirmado via WhatsApp', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $user->id,
                    'titulo' => $appointment->titulo,
                ]);

                return [
                    "✅ Seu atendimento foi *CONFIRMADO* com sucesso!",
                    ['command' => 'confirmar', 'appointment_id' => $appointment->id],
                ];
            }

            return ['⚠️ Nenhum compromisso pendente encontrado para confirmar.', ['command' => 'confirmar_vazio']];
        }

        // 🔹 Cliente respondeu "2" → marcar como CANCELADO
        if (in_array($normalized, ['2', 'CANCELAR', 'NAO', 'NÃO'])) {
            if ($appointment) {
                $oldStatus = $appointment->status;
                $appointment->update(['status' => 'cancelado']);

                $mensagemCancelamento = "❌ Seu atendimento de *{$appointment->titulo}* foi *CANCELADO* com sucesso!\n\n📅 Data: {$appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i')}\n\n💬 Para remarcar, entre em contato conosco.";

                Log::info('❌ Compromisso cancelado via WhatsApp', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $user->id,
                    'titulo' => $appointment->titulo,
                    'status_anterior' => $oldStatus,
                    'mensagem' => $mensagemCancelamento,
                ]);

                return [
                    $mensagemCancelamento,
                    ['command' => 'cancelar', 'appointment_id' => $appointment->id],
                ];
            }

            return ['⚠️ Nenhum compromisso pendente encontrado para cancelar.', ['command' => 'cancelar_vazio']];
        }

        // 🔹 Menu de ajuda
        if ($normalized === 'MENU' || $normalized === 'AJUDA') {
            return [$this->menuMessage(), ['command' => 'menu']];
        }

        // 🔹 Listar compromissos
        if ($normalized === 'LISTAR') {
            return [$this->listAppointmentsMessage($user), ['command' => 'listar']];
        }

        // 🔹 Criar novo compromisso
        if (Str::startsWith($normalized, 'CRIAR')) {
            return $this->createAppointmentFromCommand($user, $body);
        }

        // 🔹 Nenhum comando reconhecido
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
