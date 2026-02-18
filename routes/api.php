<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Rotas de Assinatura (Subscription)
|--------------------------------------------------------------------------
*/

// Rotas públicas (não requerem assinatura)
Route::middleware('auth:sanctum')->group(function () {
    // Lista os planos disponíveis
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);

    // Cria uma nova assinatura (gera link de pagamento)
    Route::post('/subscription/create', [SubscriptionController::class, 'create']);

    // Retorna a assinatura atual do usuário
    Route::get('/subscription/current', [SubscriptionController::class, 'current']);

    // Histórico de assinaturas
    Route::get('/subscription/history', [SubscriptionController::class, 'history']);

    // Cancela a assinatura
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| Rotas de Webhook
|--------------------------------------------------------------------------
*/

// Webhook do Mercado Pago (não requer autenticação)
Route::post('/webhook/mercadopago', [WebhookController::class, 'mercadopago']);

/*
|--------------------------------------------------------------------------
| Webhook do WhatsApp (API Brasil)
|--------------------------------------------------------------------------
| Aceita GET (verificação/validação da URL) e POST (recebimento de mensagens).
*/
Route::match(['get', 'post'], '/webhooks/whatsapp', function (Request $request) {
    // GET = verificação do webhook pelo painel
    if ($request->isMethod('get')) {
        return response()->json(['status' => 'ok', 'message' => 'Webhook ativo']);
    }

    // POST = mensagem recebida → delega ao controller
    return app(\App\Http\Controllers\Webhook\WhatsAppWebhookController::class)($request, app(\App\Services\WhatsAppService::class));
})->name('api.webhooks.whatsapp');

/*
|--------------------------------------------------------------------------
| Webhook: Atualização de QR Code (API Brasil)
|--------------------------------------------------------------------------
| Recebe notificação quando um novo QR Code é gerado para o dispositivo.
| URL para configurar na API Brasil: https://agendoo.tech/api/webhooks/qrcode
*/
Route::match(['get', 'post'], '/webhooks/qrcode', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json(['status' => 'ok', 'message' => 'Webhook QR Code ativo']);
    }

    \Log::info('📱 Webhook QR Code recebido', $request->all());

    $deviceId = data_get($request, 'device_id')
        ?? data_get($request, 'data.device_id')
        ?? data_get($request, 'session')
        ?? data_get($request, 'data.session')
        ?? null;

    $qrcode = data_get($request, 'qrcode')
        ?? data_get($request, 'data.qrcode')
        ?? data_get($request, 'data.data.qrcode')
        ?? null;

    if ($deviceId) {
        $empresa = \App\Models\User::where('tipo', 'empresa')
            ->where(function ($q) use ($deviceId) {
                $q->where('apibrasil_device_id', $deviceId)
                    ->orWhere('apibrasil_device_token', $deviceId);
            })
            ->first();

        if ($empresa) {
            $empresa->update([
                'apibrasil_qrcode_status' => 'pending',
            ]);

            \Log::info('📱 QR Code atualizado para empresa', [
                'empresa_id' => $empresa->id,
                'empresa_nome' => $empresa->name,
                'has_qrcode' => !empty($qrcode),
            ]);
        } else {
            \Log::warning('📱 QR Code webhook: nenhuma empresa encontrada', [
                'device_id' => $deviceId,
            ]);
        }
    }

    return response()->json(['status' => 'ok']);
})->name('api.webhooks.qrcode');

/*
|--------------------------------------------------------------------------
| Webhook: Status do Dispositivo (API Brasil)
|--------------------------------------------------------------------------
| Recebe notificação quando o status do dispositivo muda (online/offline).
| URL para configurar na API Brasil: https://agendoo.tech/api/webhooks/device-status
*/
Route::match(['get', 'post'], '/webhooks/device-status', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json(['status' => 'ok', 'message' => 'Webhook Device Status ativo']);
    }

    \Log::info('📡 Webhook Status do Dispositivo recebido', $request->all());

    $deviceId = data_get($request, 'device_id')
        ?? data_get($request, 'data.device_id')
        ?? data_get($request, 'session')
        ?? data_get($request, 'data.session')
        ?? null;

    $status = data_get($request, 'status')
        ?? data_get($request, 'data.status')
        ?? data_get($request, 'state')
        ?? data_get($request, 'data.state')
        ?? null;

    if ($deviceId) {
        $empresa = \App\Models\User::where('tipo', 'empresa')
            ->where(function ($q) use ($deviceId) {
                $q->where('apibrasil_device_id', $deviceId)
                    ->orWhere('apibrasil_device_token', $deviceId);
            })
            ->first();

        if ($empresa) {
            // Mapeia status da API Brasil para o nosso enum
            $mappedStatus = match (strtolower((string) $status)) {
                'connected', 'online', 'open' => 'connected',
                'disconnected', 'offline', 'close', 'closed' => 'disconnected',
                default => 'pending',
            };

            $empresa->update([
                'apibrasil_qrcode_status' => $mappedStatus,
            ]);

            \Log::info('📡 Status do dispositivo atualizado', [
                'empresa_id' => $empresa->id,
                'empresa_nome' => $empresa->name,
                'status_original' => $status,
                'status_mapeado' => $mappedStatus,
            ]);
        } else {
            \Log::warning('📡 Device Status webhook: nenhuma empresa encontrada', [
                'device_id' => $deviceId,
            ]);
        }
    }

    return response()->json(['status' => 'ok']);
})->name('api.webhooks.device-status');
