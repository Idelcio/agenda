<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\ChatbotMessage;
use App\Models\User;

class MetaChatController extends Controller
{
    /**
     * Interface principal de Chat para clientes Meta.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Verifica se é cliente Meta (segurança extra)
        if ($user->whatsapp_driver !== 'meta') {
            // Em produção redirecionaríamos, mas em dev vamos deixar passar ou mostrar aviso
            // return redirect()->route('dashboard')->with('error', 'Acesso restrito a clientes Meta.');
        }

        // Agrupa mensagens por número de telefone para listar conversas
        $conversationsData = ChatbotMessage::where('user_id', $user->id)
            ->select('whatsapp_numero', DB::raw('MAX(created_at) as last_time'))
            ->groupBy('whatsapp_numero')
            ->orderByDesc('last_time')
            ->get();

        $conversations = $conversationsData->map(function ($item) use ($user) {
            $phone = $item->whatsapp_numero;

            // Busca última mensagem
            $lastMsg = ChatbotMessage::where('user_id', $user->id)
                ->where('whatsapp_numero', $phone)
                ->latest()
                ->first();

            // Tenta encontrar o cliente pelo número (busca simples)
            $client = User::where('user_id', $user->id)
                ->where('whatsapp_number', 'LIKE', "%" . substr($phone, -8)) // Tenta casar os ultimos 8 digitos
                ->first();

            // Verifica janela de 24h (se a última mensagem de ENTRADA foi há menos de 24h)
            $lastIncoming = ChatbotMessage::where('user_id', $user->id)
                ->where('whatsapp_numero', $phone)
                ->where('direcao', 'entrada')
                ->latest()
                ->first();

            $windowOpen = false;
            $windowExpiresAt = null;

            if ($lastIncoming && $lastIncoming->created_at->diffInHours(now()) < 24) {
                $windowOpen = true;
                $windowExpiresAt = $lastIncoming->created_at->addHours(24);
            }

            return [
                'id' => $phone, // Usamos o telefone como ID único na lista por enquanto
                'phone' => $phone,
                'name' => $client ? $client->name : '+' . $phone,
                'last_message' => $lastMsg ? $lastMsg->conteudo : '',
                'last_time' => $item->last_time,
                'unread' => 0, // TODO: Implementar contagem de não lidos
                'window_open' => $windowOpen,
                'window_expires_at' => $windowExpiresAt,
            ];
        });

        return view('meta.chat.index', [
            'conversations' => $conversations,
            'activeConversation' => null
        ]);
    }

    /**
     * API Interna: Busca mensagens de uma conversa específica.
     */
    public function fetchMessages(Request $request, $phone)
    {
        $user = $request->user();

        $messages = ChatbotMessage::where('user_id', $user->id)
            ->where('whatsapp_numero', $phone)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'direction' => $msg->direcao, // 'entrada' ou 'saida'
                    'content' => $msg->conteudo,
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->format('d/m/Y'),
                    'timestamp' => $msg->created_at->timestamp,
                ];
            });

        return response()->json(['messages' => $messages]);
    }
    /**
     * Envia uma mensagem de texto simples via Meta Cloud API.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $user = $request->user();

        // Verifica as credenciais
        if (empty($user->meta_access_token) || empty($user->meta_phone_id)) {
            return response()->json(['error' => 'Credenciais da Meta não configuradas.'], 400);
        }

        $phoneId = $user->meta_phone_id;
        $token = $user->meta_access_token;
        $to = $request->phone; // O número deve estar no formato internacional sem + (ex: 5511999999999)

        // URL da API (v18.0 é uma versão estável recente)
        $url = "https://graph.facebook.com/v18.0/{$phoneId}/messages";

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $request->message
                    ]
                ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Salva a mensagem enviada no banco
                ChatbotMessage::create([
                    'user_id' => $user->id,
                    'external_id' => $responseData['messages'][0]['id'] ?? null,
                    'whatsapp_numero' => $to,
                    'direcao' => 'saida',
                    'conteudo' => $request->message,
                    'payload' => $responseData,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ]);
            } else {
                return response()->json([
                    'error' => 'Erro na API Meta',
                    'details' => $response->json()
                ], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno ao enviar mensagem',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envia um template de mensagem (para iniciar conversas).
     */
    public function sendTemplate(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = $request->user();

        if (empty($user->meta_access_token) || empty($user->meta_phone_id)) {
            return response()->json(['error' => 'Credenciais da Meta não configuradas.'], 400);
        }

        $phoneId = $user->meta_phone_id;
        $token = $user->meta_access_token;
        $to = $request->phone;

        $url = "https://graph.facebook.com/v22.0/{$phoneId}/messages";

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'template',
                    'template' => [
                        'name' => 'hello_world', // Template padrão que sempre existe
                        'language' => [
                            'code' => 'en_US'
                        ]
                    ]
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Template enviado! Agora você tem 24h para enviar mensagens de texto livre.',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'error' => 'Erro na API Meta',
                    'details' => $response->json()
                ], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno ao enviar template',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
