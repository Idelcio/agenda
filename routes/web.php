<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhook\WhatsAppWebhookController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
    Route::get('/agenda/eventos', [AppointmentController::class, 'events'])->name('agenda.events');
    Route::patch('/agenda/{appointment}/status', [AppointmentController::class, 'toggleStatus'])->name('agenda.status');
    Route::patch('/agenda/{appointment}/status/{status}', [AppointmentController::class, 'updateStatus'])->name('agenda.update-status');
    Route::post('/agenda/{appointment}/lembrar', [AppointmentController::class, 'sendReminder'])->name('agenda.reminder');
    Route::post('/agenda/whatsapp/rapido', [AppointmentController::class, 'sendQuickMessage'])->name('agenda.quick-whatsapp');
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

require __DIR__ . '/auth.php';
