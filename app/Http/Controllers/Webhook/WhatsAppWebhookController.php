<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ChatbotMessage;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Support\WhatsAppMessageFingerprint;
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
        Log::info('🚀 Webhook bruto recebido', $request->all());
        // 1️⃣ Detecta o device_id vindo do webhook
        $deviceId = data_get($request, 'data.device_id')
            ?? data_get($request, 'data.data.device_id')
            ?? data_get($request, 'data.data.session')
            ?? null;

        // 🔹 Detecta o session token (para mensagens recebidas)
        $sessionToken = data_get($request, 'session')
            ?? data_get($request, 'data.session')
            ?? null;

        // 2️⃣ Busca a empresa que está usando esse device_id
        $empresa = null;

        if ($deviceId) {
            $empresa = User::where('tipo', 'empresa')
                ->where('apibrasil_device_id', $deviceId)
                ->first();
        }

        // 3️⃣ fallback: tenta localizar pela origem da mensagem
        if (!$empresa) {
            $fromNumber = $this->normalizeNumber(
                data_get($request, 'data.data.from') ??
                    data_get($request, 'data.from')
            );

            $empresa = User::where('tipo', 'empresa')
                ->where('whatsapp_number', $fromNumber)
                ->first();
        }

        // 4️⃣ fallback: tenta localizar pelo session token
        if (!$empresa && $sessionToken) {
            $empresa = User::where('tipo', 'empresa')
                ->where('apibrasil_device_token', $sessionToken)
                ->first();
        }

        if (!$empresa) {
            Log::warning("❌ Nenhuma empresa correspondente ao webhook.", [
                'device_id' => $deviceId,
                'session_token' => $sessionToken,
                'from' => $fromNumber ?? null,
            ]);
            return response('OK', 200);
        }

        // 4️⃣ Ajusta o WhatsAppService para usar as credenciais dessa empresa
        $whatsApp->setDeviceCredentials(
            $empresa->apibrasil_device_token,
            $empresa->apibrasil_device_id,
        );

        Log::info("🏢 Credenciais carregadas da empresa correta", [
            'empresa_id' => $empresa->id,
            'device_id' => $empresa->apibrasil_device_id,
            'device_token' => $empresa->apibrasil_device_token,
        ]);




        // 🔹 Tipo de evento vindo da API Brasil
        $eventType = data_get($request, 'data.wook'); // Ex: "RECEIVE_MESSAGE", "MESSAGE_STATUS"
        $type = data_get($request, 'data.data.type'); // Ex: "text", "image", etc.

        // 🔹 Considera "RECEIVE_MESSAGE" e "MESSAGE" como mensagens válidas
        if (!in_array($eventType, ['RECEIVE_MESSAGE', 'MESSAGE'])) {
            Log::info("Webhook ignorado (evento: {$eventType})");
            return response('OK', Response::HTTP_OK);
        }


        // 🔹 Captura número e conteúdo corretamente
        // API Brasil pode fornecer o remetente em diferentes campos; prioriza o campo interno do evento
        $from = $this->normalizeNumber(
            data_get($request, 'data.data.from') ??
                data_get($request, 'data.data.remote') ??
                data_get($request, 'data.from') ??
                data_get($request, 'data.remote') ??
                null
        );

        // 🔹 IGNORA MENSAGENS ENVIADAS PELO PRÓPRIO SISTEMA (fromMe = true)
        // API Brasil varia a localização do flag; inspecionamos todas as possibilidades
        $isFromMe = (bool) (
            data_get($request, 'data.data.id.fromMe') ??
            data_get($request, 'data.data.fromMe') ??
            data_get($request, 'data.fromMe') ??
            false
        );

        if ($isFromMe) {
            Log::info('🚫 Mensagem ignorada (enviada pelo próprio sistema)', [
                'from' => $from,
            ]);
            return response('OK', Response::HTTP_OK);
        }

        // ✅ Corrigido: pega exatamente o body certo ("2" no teu exemplo)
        $body = trim((string)(
            data_get($request, 'data.data.body') ??
            data_get($request, 'data.body') ??
            data_get($request, 'data.content') ??
            data_get($request, 'body') ??
            ''
        ));
        $sessionId =
            data_get($request, 'data.session') ??
            data_get($request, 'data.device.id') ??
            data_get($request, 'data.data.session') ??
            data_get($request, 'data.data.device_id') ??
            null;

        // 🔹 Pega legenda caso seja mídia
        $caption = data_get($request, 'data.data.caption');
        $body = $body ?: $caption;

        Log::info('📩 Webhook recebido', [
            'from' => $from,
            'body' => $body,
            'raw' => $request->all(),
        ]);

        // 🔹 Extrai ID único da mensagem para evitar duplicatas
        $messageId = WhatsAppMessageFingerprint::forPayload($request->all(), $from, $body);

        // 🔹 Verifica se esta mensagem já foi processada
        if (ChatbotMessage::where('external_id', $messageId)->exists()) {
            Log::info('⚠️ Mensagem duplicada detectada, ignorando', [
                'message_id' => $messageId,
                'from' => $from,
            ]);
            return response('OK', Response::HTTP_OK);
        }

        // 🔹 Cria variação do número (com/sem o 9) para busca mais flexível
        $fromVariation = null;
        if ($from && preg_match('/^55(\d{2})9(\d{8})$/', $from, $matches)) {
            // Se tem 9, cria versão sem o 9: 5511987654321 -> 551187654321
            $fromVariation = '55' . $matches[1] . $matches[2];
        } elseif ($from && preg_match('/^55(\d{2})(\d{8})$/', $from, $matches)) {
            // Se não tem 9, cria versão com o 9: 551187654321 -> 5511987654321
            $fromVariation = '55' . $matches[1] . '9' . $matches[2];
        }

        Log::info('🔍 Buscando usuário/cliente', [
            'numero_original' => $from,
            'numero_variacao' => $fromVariation,
        ]);

        // 🔹 Localiza o usuário pelo número do WhatsApp (principal ou cliente vinculado)
        // 🔹 Busca tanto com o número original quanto com a variação (com/sem o 9)
        $user = $from ? User::where(function ($query) use ($from, $fromVariation) {
            $query->where('whatsapp_number', $from);
            if ($fromVariation) {
                $query->orWhere('whatsapp_number', $fromVariation);
            }
        })->first() : null;

        // 🔹 Se não encontrou, verifica se é um cliente vinculado a alguma empresa
        if (!$user && $from) {
            $user = User::whereHas('clientes', function ($query) use ($from, $fromVariation) {
                $query->where(function ($q) use ($from, $fromVariation) {
                    $q->where('whatsapp_number', $from);
                    if ($fromVariation) {
                        $q->orWhere('whatsapp_number', $fromVariation);
                    }
                });
            })->first();

            // Se encontrou a empresa, pega o cliente real
            if ($user) {
                $user = $user->clientes()->where(function ($query) use ($from, $fromVariation) {
                    $query->where('whatsapp_number', $from);
                    if ($fromVariation) {
                        $query->orWhere('whatsapp_number', $fromVariation);
                    }
                })->first();
            }
        }

        Log::info('📋 Resultado da busca de usuário', [
            'encontrado' => $user ? 'sim' : 'não',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
        ]);

        // 🔹 Registra a mensagem recebida
        ChatbotMessage::create([
            'user_id' => $user?->id,
            'whatsapp_numero' => $from,
            'direcao' => 'entrada',
            'conteudo' => $body,
            'payload' => $request->all(),
            'external_id' => $messageId, // 🔹 Armazena o ID para evitar duplicatas
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
        if (is_array($meta)) {
            $meta['session'] = $sessionId;
        } else {
            $meta = ['session' => $sessionId];
        }

        // 🔹 Responde o usuário
        $this->sendReply($whatsApp, $from, $reply, $user, $meta);

        return response('OK', Response::HTTP_OK);
    }



    private function handleCommand(User $user, string $whatsappNumber, string $body): array
    {
        $normalized = Str::upper(Str::of($body)->trim());

        // 🔹 Normaliza o número removendo caracteres especiais
        $cleanNumber = preg_replace('/\D+/', '', $whatsappNumber);

        // 🔹 Cria variação do número (com/sem o 9) para busca mais flexível
        $numberVariation = null;
        if (preg_match('/^55(\d{2})9(\d{8})$/', $cleanNumber, $matches)) {
            // Se tem 9, cria versão sem o 9: 5511987654321 -> 551187654321
            $numberVariation = '55' . $matches[1] . $matches[2];
        } elseif (preg_match('/^55(\d{2})(\d{8})$/', $cleanNumber, $matches)) {
            // Se não tem 9, cria versão com o 9: 551187654321 -> 5511987654321
            $numberVariation = '55' . $matches[1] . '9' . $matches[2];
        }

        Log::info('🔍 Buscando compromisso', [
            'whatsapp_original' => $whatsappNumber,
            'whatsapp_limpo' => $cleanNumber,
            'whatsapp_variacao' => $numberVariation,
            'comando' => $normalized,
        ]);

        // 🔹 Busca o compromisso mais recente com lembrete enviado E QUE AINDA NÃO FOI RESPONDIDO
        // 🔹 Busca tanto com o número original quanto com a variação (com/sem o 9)
        $appointment = Appointment::query()
            ->where(function ($query) use ($cleanNumber, $numberVariation) {
                $query->where('whatsapp_numero', $cleanNumber);
                if ($numberVariation) {
                    $query->orWhere('whatsapp_numero', $numberVariation);
                }
            })
            ->where('status_lembrete', 'enviado') // Só lembretes enviados
            ->where('status', 'pendente') // Só compromissos ainda pendentes (não confirmados nem cancelados)
            ->latest('lembrete_enviado_em')
            ->first();

        Log::info('📋 Resultado da busca', [
            'encontrado' => $appointment ? 'sim' : 'não',
            'appointment_id' => $appointment?->id,
            'titulo' => $appointment?->titulo,
            'status_atual' => $appointment?->status,
        ]);

        // 🔹 Cliente respondeu "1" → CONFIRMAR
        if (in_array($normalized, ['1', 'CONFIRMAR', 'SIM', 'OK'])) {
            if ($appointment) {
                $appointment->update([
                    'status' => 'confirmado',
                    'status_lembrete' => 'respondido', // 🔹 Marca como respondido
                ]);

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

            // Se não há lembrete enviado, não responde
            Log::info('⚠️ Nenhum lembrete encontrado para confirmar, ignorando...');
            return [null, ['command' => 'ignored']];
        }

        // 🔹 Cliente respondeu "2" → CANCELAR
        if (in_array($normalized, ['2', 'CANCELAR', 'NAO', 'NÃO'])) {
            if ($appointment) {
                $oldStatus = $appointment->status;
                $appointment->update([
                    'status' => 'cancelado',
                    'status_lembrete' => 'respondido', // 🔹 Marca como respondido
                ]);

                $mensagemCancelamento = "❌ Seu atendimento de *{$appointment->titulo}* foi *CANCELADO* com sucesso!\n\n📅 Data: {$appointment->inicio->timezone(config('app.timezone'))->format('d/m/Y H:i')}\n\n💬 Para remarcar, entre em contato conosco.";

                Log::info('❌ Compromisso cancelado via WhatsApp', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $user->id,
                    'titulo' => $appointment->titulo,
                    'status_anterior' => $oldStatus,
                ]);

                return [
                    $mensagemCancelamento,
                    ['command' => 'cancelar', 'appointment_id' => $appointment->id],
                ];
            }

            // Se não há lembrete enviado, não responde
            Log::info('⚠️ Nenhum lembrete encontrado para cancelar, ignorando...');
            return [null, ['command' => 'ignored']];
        }

        // 🔹 Ignora qualquer outra mensagem (sem resposta automática)
        Log::info('💤 Mensagem ignorada (fora de contexto de lembrete)', [
            'comando' => $normalized,
            'whatsapp' => $whatsappNumber,
        ]);

        return [null, ['command' => 'ignored']];
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

    private function sendReply(WhatsAppService $service, string $to, ?string $message, ?User $user = null, ?array $meta = null): void
    {
        if (!$message) {
            Log::info('⚠️ Mensagem vazia, não enviando resposta');
            return;
        }

        try {
            $sessionToken = data_get($meta, 'session');

            Log::info('🔍 Sessão detectada no webhook', [
                'sessionToken' => $sessionToken,
                'empresaEncontrada' => null,
            ]);

            if (! $sessionToken) {
                Log::warning('❌ Nenhuma sessão informada no webhook; abortando envio.');
                return;
            }

            $empresaAtiva = User::where('tipo', 'empresa')
                ->where('apibrasil_device_token', $sessionToken)
                ->first();

            if (! $empresaAtiva) {
                Log::warning('❌ Nenhuma empresa correspondente à sessão. Abortando envio.', [
                    'session' => $sessionToken,
                    'to' => $to,
                    'user_id' => $user?->id,
                ]);
                return;
            }

            Log::info('🔍 Sessão detectada no webhook', [
                'sessionToken' => $sessionToken,
                'empresaEncontrada' => $empresaAtiva->id,
            ]);

            // 🔹 Testa a(s) sessão(ões) da empresa até encontrar uma CONECTADA
            // 🔹 Verifica se a sessão está conectada
            try {
                $status = $service->checkDeviceStatus($empresaAtiva->apibrasil_device_token);
                if (!($status['connected'] ?? false)) {
                    Log::error('❌ Sessão não conectada para esta empresa', [
                        'empresa_id' => $empresaAtiva->id,
                        'empresa_nome' => $empresaAtiva->name,
                        'session' => $sessionToken,
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ Erro ao verificar status da sessão', [
                    'empresa_id' => $empresaAtiva->id,
                    'empresa_nome' => $empresaAtiva->name,
                    'erro' => $e->getMessage(),
                    'session' => $sessionToken,
                ]);
                return;
            }

            // 🔹 Usa as credenciais da empresa CONECTADA
            $service->setDeviceCredentials(
                $empresaAtiva->apibrasil_device_token,
                $empresaAtiva->apibrasil_device_id
            );

            Log::info('📤 Enviando mensagem via sessão ativa', [
                'empresa_id' => $empresaAtiva->id,
                'empresa_nome' => $empresaAtiva->name,
                'para' => $to,
            ]);

            $service->sendMessage($to, $message);

            // Registra mensagem enviada
            ChatbotMessage::create([
                'user_id' => $user?->id,
                'whatsapp_numero' => $to,
                'direcao' => 'saida',
                'conteudo' => $message,
                'payload' => $meta,
            ]);
        } catch (RuntimeException $exception) {
            Log::warning('WhatsApp service not configured', ['exception' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            Log::error('Erro ao enviar mensagem de WhatsApp', ['exception' => $exception->getMessage()]);
        }
    }

    // private function isAuthorized(Request $request): bool
    // {
    //     $secret = config('services.whatsapp.webhook_secret');

    //     if (! $secret) {
    //         return true;
    //     }

    //     $provided = $request->header('X-Webhook-Secret', $request->input('secret'));

    //     return hash_equals($secret, (string) $provided);
    // }

    private function isAuthorized(Request $request): bool
    {
        // 🔓 Desativado temporariamente para testes
        return true;
    }


    private function normalizeNumber(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        // Remove prefixos e caracteres não numéricos
        $clean = preg_replace('/\D+/', '', str_replace('whatsapp:', '', $value));

        // 🔹 Adiciona DDI do Brasil se não tiver
        if (! str_starts_with($clean, '55')) {
            $clean = '55' . $clean;
        }

        // 🔹 NÃO força inserção do "9" — assume que vem correto da origem

        // 🔹 Remove zeros à esquerda se houver
        $clean = ltrim($clean, '0');

        // 🔹 Log opcional para depuração (pode remover depois)
        Log::info('📞 Número normalizado', [
            'original' => $value,
            'final' => $clean,
        ]);

        return $clean;
    }
}
