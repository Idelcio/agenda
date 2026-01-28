<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuickMessageTemplateController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Webhook\WhatsAppWebhookController;
use App\Http\Controllers\WhatsAppSetupController;
use Illuminate\Support\Facades\Route;
use App\Services\WhatsAppService;

/*
|--------------------------------------------------------------------------
| Rota de Teste (envio manual de mensagem)
|--------------------------------------------------------------------------
| Usa o serviço de WhatsApp para enviar mensagem e testar a integração.
| ⚠️ Recomendo usar sendMessage() (que já trata exceções e formato padrão),
| a menos que seu WhatsAppService defina sendText() como método personalizado.
*/

Route::get('/teste-whatsapp', function (WhatsAppService $whatsApp) {
    $resposta = $whatsApp->sendMessage('5551984871703', 'Mensagem de teste via API Brasil');
    dd($resposta);
});

// Páginas obrigatórias para Meta App
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/data-deletion', function () {
    return view('data-deletion');
})->name('data-deletion');

/*
|--------------------------------------------------------------------------
| Lembretes pendentes (rota autenticada)
|--------------------------------------------------------------------------
*/
Route::get('/agenda/lembretes-pendentes', [AppointmentController::class, 'lembretesPendentes'])
    ->middleware(['auth'])
    ->name('agenda.lembretes-pendentes');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/termos', function () {
    return view('terms');
})->name('terms');

/*
|--------------------------------------------------------------------------
| Rotas de Setup do WhatsApp (autenticadas)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/setup-whatsapp', [WhatsAppSetupController::class, 'index'])->name('setup-whatsapp.index');
    Route::post('/setup-whatsapp/save-credentials', [WhatsAppSetupController::class, 'saveDeviceCredentials'])->name('setup-whatsapp.save-credentials');
    Route::get('/setup-whatsapp/check-connection', [WhatsAppSetupController::class, 'checkConnection'])->name('setup-whatsapp.check-connection');
    Route::post('/setup-whatsapp/complete', [WhatsAppSetupController::class, 'completeSetup'])->name('setup-whatsapp.complete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/setup-meta', [App\Http\Controllers\FacebookSetupController::class, 'index'])->name('setup-meta.index');
    Route::post('/setup-meta/save', [App\Http\Controllers\FacebookSetupController::class, 'store'])->name('setup-meta.store');

    // Embedded Signup OAuth Flow
    Route::get('/auth/meta/redirect', [App\Http\Controllers\MetaEmbeddedSignupController::class, 'redirect'])->name('meta.oauth.redirect');
    Route::get('/auth/meta/callback', [App\Http\Controllers\MetaEmbeddedSignupController::class, 'callback'])->name('meta.oauth.callback');
    Route::post('/auth/meta/disconnect', [App\Http\Controllers\MetaEmbeddedSignupController::class, 'disconnect'])->name('meta.oauth.disconnect');

    Route::get('/meta/chat', [App\Http\Controllers\MetaChatController::class, 'index'])->name('meta.chat.index');
    Route::get('/meta/chat/{phone}/messages', [App\Http\Controllers\MetaChatController::class, 'fetchMessages'])->name('meta.chat.messages');
    Route::post('/meta/chat/send', [App\Http\Controllers\MetaChatController::class, 'sendMessage'])->name('meta.chat.send');
    Route::post('/meta/chat/send-template', [App\Http\Controllers\MetaChatController::class, 'sendTemplate'])->name('meta.chat.send-template');
    Route::get('/meta/webhook-monitor', function () {
        return view('meta.webhook-monitor');
    })->name('meta.webhook-monitor');
});

// Webhook da Meta (sem autenticação - a Meta precisa acessar publicamente)
Route::get('/webhooks/meta', [App\Http\Controllers\MetaWebhookController::class, 'verify'])->name('webhooks.meta.verify');
Route::post('/webhooks/meta', [App\Http\Controllers\MetaWebhookController::class, 'handle'])->name('webhooks.meta.handle');

/*
|--------------------------------------------------------------------------
| Rotas de Assinatura (autenticadas, sem middleware de assinatura)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Web\SubscriptionWebController;

// Rotas de retorno do Mercado Pago (públicas - não requerem autenticação)
Route::get('/payment/success', [SubscriptionWebController::class, 'success'])->name('payment.success');
Route::get('/payment/failure', [SubscriptionWebController::class, 'failure'])->name('payment.failure');
Route::get('/payment/pending', [SubscriptionWebController::class, 'pending'])->name('payment.pending');

Route::middleware(['auth'])->group(function () {
    Route::get('/subscription/plans', [SubscriptionWebController::class, 'plans'])->name('subscription.plans');
    Route::post('/subscription/checkout', [SubscriptionWebController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/current', [SubscriptionWebController::class, 'current'])->name('subscription.current');
    Route::get('/subscription/history', [SubscriptionWebController::class, 'history'])->name('subscription.history');
});

Route::middleware(['auth', 'verified', 'subscription'])->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
    Route::get('/agenda/eventos', [AppointmentController::class, 'events'])->name('agenda.events');
    Route::get('/agenda/pdf-semanal', [AppointmentController::class, 'gerarPdfSemanal'])->name('agenda.pdf-semanal');
    Route::patch('/agenda/{appointment}/status', [AppointmentController::class, 'toggleStatus'])->name('agenda.status');
    Route::patch('/agenda/{appointment}/status/{status}', [AppointmentController::class, 'updateStatus'])->name('agenda.update-status');
    Route::post('/agenda/{appointment}/lembrar', [AppointmentController::class, 'sendReminder'])->name('agenda.reminder');
    Route::post('/agenda/whatsapp/rapido', [AppointmentController::class, 'sendQuickMessage'])->name('agenda.quick-whatsapp');
    Route::post('/agenda/quick-messages', [QuickMessageTemplateController::class, 'store'])->name('agenda.quick-messages.store');
    Route::patch('/agenda/quick-messages/{template}', [QuickMessageTemplateController::class, 'update'])->name('agenda.quick-messages.update');
    Route::delete('/agenda/quick-messages/{template}', [QuickMessageTemplateController::class, 'destroy'])->name('agenda.quick-messages.destroy');
    Route::resource('agenda', AppointmentController::class)
        ->parameters(['agenda' => 'appointment'])
        ->except(['show']);

    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::post('/clientes/send-mass-message', [ClienteController::class, 'sendMassMessage'])->name('clientes.send-mass-message');

    // Tags
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::patch('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    Route::post('/tags/attach', [TagController::class, 'attachToCliente'])->name('tags.attach');
    Route::post('/tags/detach', [TagController::class, 'detachFromCliente'])->name('tags.detach');
    Route::post('/tags/attach-multiple', [TagController::class, 'attachToMultipleClientes'])->name('tags.attach-multiple');
    Route::post('/tags/detach-multiple', [TagController::class, 'detachFromMultipleClientes'])->name('tags.detach-multiple');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Webhook público do WhatsApp
|--------------------------------------------------------------------------
| ⚠️ Essa rota NÃO deve ter middleware de autenticação.
| O provedor (ex: API Brasil) precisa conseguir acessá-la externamente.
*/
Route::post('/webhooks/whatsapp', WhatsAppWebhookController::class)
    ->name('webhooks.whatsapp');

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/super-admin-routes.php';

require __DIR__ . '/auth.php';
