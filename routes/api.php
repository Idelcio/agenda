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
