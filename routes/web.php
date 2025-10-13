<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhook\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;
use App\Services\WhatsAppService;

Route::get('/teste-whatsapp', function (WhatsAppService $whatsApp) {
    $resposta = $whatsApp->sendText('5551984871703', 'Mensagem de teste via API Brasil');
    dd($resposta);
});
Route::get('/agenda/lembretes-pendentes', [AppointmentController::class, 'lembretesPendentes'])
    ->middleware(['auth'])
    ->name('agenda.lembretes-pendentes');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
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
    Route::resource('agenda', AppointmentController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/webhooks/whatsapp', WhatsAppWebhookController::class)->name('webhooks.whatsapp');

require __DIR__ . '/auth.php';
