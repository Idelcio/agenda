<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class MetaEmbeddedSignupController extends Controller
{
    /**
     * Redireciona o usuário para o fluxo de Embedded Signup da Meta
     */
    public function redirect(Request $request)
    {
        $user = $request->user();

        $configId = config('services.meta.config_id');
        $redirectUri = config('services.meta.redirect_uri');

        // Salva o user_id na sessão para recuperar no callback
        session(['meta_signup_user_id' => $user->id]);

        // URL do Facebook Login para Embedded Signup
        $url = "https://www.facebook.com/v22.0/dialog/oauth?" . http_build_query([
            'client_id' => config('services.meta.app_id'),
            'redirect_uri' => $redirectUri,
            'config_id' => $configId,
            'response_type' => 'code',
            'scope' => 'whatsapp_business_management,whatsapp_business_messaging',
            'state' => csrf_token(), // Proteção CSRF
        ]);

        return redirect($url);
    }

    /**
     * Callback após o usuário autorizar no Facebook
     */
    public function callback(Request $request)
    {
        // Valida o state (CSRF)
        if ($request->state !== csrf_token()) {
            return redirect()->route('setup-meta.index')
                ->withErrors(['error' => 'Erro de segurança. Tente novamente.']);
        }

        // Verifica se houve erro
        if ($request->has('error')) {
            Log::error('Meta Embedded Signup Error', [
                'error' => $request->error,
                'error_description' => $request->error_description
            ]);

            return redirect()->route('setup-meta.index')
                ->withErrors(['error' => 'Autorização cancelada ou falhou.']);
        }

        // Troca o code por access token
        $code = $request->code;

        try {
            $response = Http::get('https://graph.facebook.com/v22.0/oauth/access_token', [
                'client_id' => config('services.meta.app_id'),
                'client_secret' => config('services.meta.app_secret'),
                'redirect_uri' => config('services.meta.redirect_uri'),
                'code' => $code,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Falha ao obter access token: ' . $response->body());
            }

            $data = $response->json();
            $accessToken = $data['access_token'];

            // Busca informações da conta WhatsApp Business conectada
            $accountInfo = $this->getWhatsAppBusinessAccount($accessToken);

            if (!$accountInfo) {
                throw new \Exception('Não foi possível obter informações da conta WhatsApp Business.');
            }

            // Recupera o usuário da sessão
            $userId = session('meta_signup_user_id');
            $user = User::find($userId);

            if (!$user) {
                throw new \Exception('Usuário não encontrado na sessão.');
            }

            // Salva as credenciais no banco
            $user->update([
                'whatsapp_driver' => 'meta',
                'meta_phone_id' => $accountInfo['phone_number_id'],
                'meta_access_token' => $accessToken,
                'meta_business_id' => $accountInfo['business_id'],
                'quota_limit' => 1000,
                'quota_usage' => 0,
            ]);

            // Limpa a sessão
            session()->forget('meta_signup_user_id');

            Log::info('Meta Embedded Signup Success', [
                'user_id' => $user->id,
                'phone_id' => $accountInfo['phone_number_id'],
            ]);

            return redirect()->route('setup-meta.index')
                ->with('success', 'WhatsApp conectado com sucesso! Você já pode usar o chat.');

        } catch (\Exception $e) {
            Log::error('Meta Embedded Signup Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('setup-meta.index')
                ->withErrors(['error' => 'Erro ao conectar: ' . $e->getMessage()]);
        }
    }

    /**
     * Busca informações da conta WhatsApp Business
     */
    private function getWhatsAppBusinessAccount($accessToken)
    {
        try {
            Log::info('Iniciando busca de conta WhatsApp Business');

            // Primeiro, busca as contas de negócio do usuário
            $response = Http::withToken($accessToken)
                ->get('https://graph.facebook.com/v22.0/me/businesses');

            Log::info('Resposta /me/businesses', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                Log::error('Falha ao buscar businesses', ['response' => $response->body()]);
                return null;
            }

            $businesses = $response->json()['data'] ?? [];

            if (empty($businesses)) {
                Log::error('Nenhum business encontrado');
                return null;
            }

            $businessId = $businesses[0]['id'];
            Log::info('Business ID encontrado', ['business_id' => $businessId]);

            // Busca as contas WhatsApp Business vinculadas
            $wabaResponse = Http::withToken($accessToken)
                ->get("https://graph.facebook.com/v22.0/{$businessId}/client_whatsapp_business_accounts");

            Log::info('Resposta /client_whatsapp_business_accounts', [
                'status' => $wabaResponse->status(),
                'body' => $wabaResponse->json()
            ]);

            if (!$wabaResponse->successful()) {
                Log::error('Falha ao buscar WABAs', ['response' => $wabaResponse->body()]);
                return null;
            }

            $wabas = $wabaResponse->json()['data'] ?? [];

            if (empty($wabas)) {
                Log::error('Nenhuma WABA encontrada');
                return null;
            }

            $wabaId = $wabas[0]['id'];
            Log::info('WABA ID encontrado', ['waba_id' => $wabaId]);

            // Busca os números de telefone da WABA
            $phoneResponse = Http::withToken($accessToken)
                ->get("https://graph.facebook.com/v22.0/{$wabaId}/phone_numbers");

            Log::info('Resposta /phone_numbers', [
                'status' => $phoneResponse->status(),
                'body' => $phoneResponse->json()
            ]);

            if (!$phoneResponse->successful()) {
                Log::error('Falha ao buscar phone numbers', ['response' => $phoneResponse->body()]);
                return null;
            }

            $phones = $phoneResponse->json()['data'] ?? [];

            if (empty($phones)) {
                Log::error('Nenhum phone number encontrado');
                return null;
            }

            Log::info('Sucesso! Dados encontrados', [
                'business_id' => $businessId,
                'waba_id' => $wabaId,
                'phone_number_id' => $phones[0]['id']
            ]);

            return [
                'business_id' => $businessId,
                'waba_id' => $wabaId,
                'phone_number_id' => $phones[0]['id'],
                'display_phone_number' => $phones[0]['display_phone_number'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching WhatsApp Business Account info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Desconecta a conta Meta
     */
    public function disconnect(Request $request)
    {
        $user = $request->user();

        $user->update([
            'meta_phone_id' => null,
            'meta_access_token' => null,
            'meta_business_id' => null,
        ]);

        return redirect()->route('setup-meta.index')
            ->with('success', 'WhatsApp desconectado com sucesso.');
    }
}
