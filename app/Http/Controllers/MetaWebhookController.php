<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ChatbotMessage;

class MetaWebhookController extends Controller
{
    /**
     * Verificação do webhook (GET) - A Meta chama isso para verificar o endpoint.
     */
    public function verify(Request $request)
    {
        // Token de verificação que você vai definir no painel da Meta
        $verifyToken = env('META_WEBHOOK_VERIFY_TOKEN', 'meu_token_secreto_123');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        // Verifica se o token está correto
        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Webhook verificado com sucesso!');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Falha na verificação do webhook', [
            'mode' => $mode,
            'token' => $token
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Recebe eventos do webhook (POST) - A Meta envia notificações aqui.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Log completo do payload para debug
        Log::info('Webhook recebido da Meta', ['payload' => $payload]);

        // Verifica se é uma notificação do WhatsApp
        if (isset($payload['object']) && $payload['object'] === 'whatsapp_business_account') {

            foreach ($payload['entry'] ?? [] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $value = $change['value'] ?? [];

                    // Identifica a empresa (User) baseada no ID do telefone
                    $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
                    $company = null;

                    if ($phoneNumberId) {
                        $company = User::where('meta_phone_id', $phoneNumberId)->first();
                    }

                    if (!$company) {
                        Log::warning('Webhook recebido para ID de telefone desconhecido ou não configurado', [
                            'phone_number_id' => $phoneNumberId
                        ]);
                        continue;
                    }

                    // Processa mensagens recebidas
                    if (isset($value['messages'])) {
                        $this->processIncomingMessages($value, $company);
                    }

                    // Processa status de mensagens enviadas
                    if (isset($value['statuses'])) {
                        $this->processMessageStatuses($value, $company);
                    }
                }
            }
        }

        // Sempre retorna 200 OK para a Meta
        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Processa mensagens recebidas de clientes.
     */
    private function processIncomingMessages($value, $company)
    {
        $messages = $value['messages'] ?? [];

        foreach ($messages as $message) {
            $from = $message['from'] ?? null;
            $messageId = $message['id'] ?? null;
            $timestamp = $message['timestamp'] ?? null;
            $type = $message['type'] ?? 'unknown';

            // Extrai o conteúdo baseado no tipo
            $content = null;
            switch ($type) {
                case 'text':
                    $content = $message['text']['body'] ?? null;
                    break;
                case 'image':
                    $content = $message['image']['caption'] ?? '[Imagem]';
                    break;
                case 'video':
                    $content = $message['video']['caption'] ?? '[Vídeo]';
                    break;
                case 'audio':
                    $content = '[Áudio]';
                    break;
                case 'document':
                    $content = $message['document']['caption'] ?? '[Documento]';
                    break;
                case 'button': // Resposta a botão
                    $content = $message['button']['text'] ?? '[Botão]';
                    break;
                case 'interactive': // Resposta a lista ou botão interativo
                    $typeInteractive = $message['interactive']['type'] ?? '';
                    if ($typeInteractive === 'button_reply') {
                        $content = $message['interactive']['button_reply']['title'] ?? '[Botão]';
                    } elseif ($typeInteractive === 'list_reply') {
                        $content = $message['interactive']['list_reply']['title'] ?? '[Lista]';
                    } else {
                        $content = '[Interativo]';
                    }
                    break;
                default:
                    $content = "[$type]";
                    break;
            }

            // Verifica se a mensagem já existe para evitar duplicidade (reentregas do webhook)
            $exists = ChatbotMessage::where('external_id', $messageId)->exists();

            if (!$exists) {
                ChatbotMessage::create([
                    'user_id' => $company->id,
                    'external_id' => $messageId,
                    'whatsapp_numero' => $from,
                    'direcao' => 'entrada',
                    'conteudo' => $content,
                    'payload' => $message,
                    // 'status' => 'received', // Se tiver coluna status
                ]);

                Log::info('Mensagem salva no banco', ['id' => $messageId, 'from' => $from]);
            }
        }
    }

    /**
     * Processa status de mensagens enviadas (enviado, entregue, lido, falhou).
     */
    private function processMessageStatuses($value, $company)
    {
        $statuses = $value['statuses'] ?? [];

        foreach ($statuses as $status) {
            $messageId = $status['id'] ?? null;
            // $recipientId = $status['recipient_id'] ?? null;
            $statusType = $status['status'] ?? 'unknown';
            // $timestamp = $status['timestamp'] ?? null;

            // Encontra a mensagem original pelo ID externo e atualiza o status se for o caso
            // Como nossa tabela ChatbotMessage pode não ter campo 'status' ainda, apenas logaremos ou 
            // se tivermos enviado nós mesmos, poderíamos ter salvo o ID em outra tabela de tracking.

            // Exemplo hipotético de atualização:
            // $msg = ChatbotMessage::where('external_id', $messageId)->first();
            // if ($msg) {
            //    $msg->status = $statusType;
            //    $msg->save();
            // }

            Log::info('Status de mensagem recebido', [
                'message_id' => $messageId,
                'status' => $statusType,
            ]);
        }
    }
}
