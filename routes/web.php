<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuickMessageTemplateController;
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

/*
|--------------------------------------------------------------------------
| Rotas de Assinatura (autenticadas, sem middleware de assinatura)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Web\SubscriptionWebController;

Route::middleware(['auth'])->group(function () {
    Route::get('/subscription/plans', [SubscriptionWebController::class, 'plans'])->name('subscription.plans');
    Route::post('/subscription/checkout', [SubscriptionWebController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/current', [SubscriptionWebController::class, 'current'])->name('subscription.current');
    Route::get('/subscription/history', [SubscriptionWebController::class, 'history'])->name('subscription.history');

    // Rotas de retorno do Mercado Pago (não requerem assinatura)
    Route::get('/payment/success', [SubscriptionWebController::class, 'success'])->name('payment.success');
    Route::get('/payment/failure', [SubscriptionWebController::class, 'failure'])->name('payment.failure');
    Route::get('/payment/pending', [SubscriptionWebController::class, 'pending'])->name('payment.pending');
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
