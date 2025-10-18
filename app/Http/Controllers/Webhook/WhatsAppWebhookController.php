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
        Log::info('🚀 Webhook bruto recebido', $request->all());


        // 🔹 Tipo de evento vindo da API Brasil
        $eventType = data_get($request, 'data.wook'); // Ex: "RECEIVE_MESSAGE", "MESSAGE_STATUS"
        $type = data_get($request, 'data.data.type'); // Ex: "text", "image", etc.

        // 🔹 Considera "RECEIVE_MESSAGE" e "MESSAGE" como mensagens válidas
        if (!in_array($eventType, ['RECEIVE_MESSAGE', 'MESSAGE'])) {
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

        // 🔹 Busca o compromisso mais recente com lembrete enviado
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

    private function sendReply(WhatsAppService $service, string $to, string $message, ?User $user = null, ?array $meta = null): void
    {
        if (!$message) {
            Log::info('⚠️ Mensagem vazia, não enviando resposta');
            return;
        }

        try {
            // 🔹 Busca TODAS as empresas com device_token configurado
            $empresas = User::where('tipo', 'empresa')
                ->whereNotNull('apibrasil_device_token')
                ->where('apibrasil_device_token', '!=', '')
                ->get();

            if ($empresas->isEmpty()) {
                Log::error('❌ Nenhuma empresa com device_token encontrada');
                return;
            }

            $empresaAtiva = null;

            // 🔹 Testa cada empresa até encontrar uma com sessão CONECTADA
            foreach ($empresas as $empresa) {
                Log::info('🔍 Testando sessão da empresa', [
                    'empresa_id' => $empresa->id,
                    'empresa_nome' => $empresa->name,
                ]);

                try {
                    // Verifica se a sessão está conectada
                    $status = $service->checkDeviceStatus($empresa->apibrasil_device_token);

                    if ($status['connected'] ?? false) {
                        $empresaAtiva = $empresa;
                        Log::info('✅ Sessão CONECTADA encontrada!', [
                            'empresa_id' => $empresa->id,
                            'empresa_nome' => $empresa->name,
                        ]);
                        break;
                    } else {
                        Log::warning('⚠️ Sessão DESCONECTADA', [
                            'empresa_id' => $empresa->id,
                            'empresa_nome' => $empresa->name,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('⚠️ Erro ao verificar status da sessão', [
                        'empresa_id' => $empresa->id,
                        'erro' => $e->getMessage(),
                    ]);
                    continue;
                }
            }

            if (!$empresaAtiva) {
                Log::error('❌ Nenhuma empresa com sessão CONECTADA encontrada');
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

        // 🔹 Corrige números sem o 9 (ex: 555184871703 → 5551984871703)
        if (strlen($clean) === 12 && str_starts_with($clean, '55')) {
            $ddd = substr($clean, 2, 2);
            $resto = substr($clean, 4);

            // Se o primeiro dígito após o DDD for entre 6 e 9, insere o 9
            if (preg_match('/^[6-9]/', $resto)) {
                $clean = '55' . $ddd . '9' . $resto;
            }
        }

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
