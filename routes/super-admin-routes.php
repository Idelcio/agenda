<?php

use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super.admin'])->group(function () {

    // Dashboard
    Route::get('/', [SuperAdminController::class, 'index'])->name('dashboard');

    // Empresas
    Route::get('/empresas', [SuperAdminController::class, 'empresas'])->name('empresas');
    Route::get('/empresas/{id}', [SuperAdminController::class, 'empresaDetalhes'])->name('empresas.detalhes');
    Route::get('/empresas/{id}/editar', [SuperAdminController::class, 'empresaEditar'])->name('empresas.editar');
    Route::put('/empresas/{id}', [SuperAdminController::class, 'empresaAtualizar'])->name('empresas.atualizar');
    Route::delete('/empresas/{id}', [SuperAdminController::class, 'empresaDeletar'])->name('empresas.deletar');

    // Ações rápidas
    Route::post('/empresas/{id}/trial', [SuperAdminController::class, 'liberarAcessoTrial'])->name('empresas.trial');
    Route::post('/empresas/{id}/toggle-acesso', [SuperAdminController::class, 'toggleAcesso'])->name('empresas.toggle-acesso');
    Route::post('/empresas/{id}/acesso-total', [SuperAdminController::class, 'liberarAcessoTotal'])->name('empresas.acesso-total');
    Route::post('/empresas/{id}/resetar-requisicoes', [SuperAdminController::class, 'resetarRequisicoes'])->name('empresas.resetar-requisicoes');

    // Relatórios
    Route::get('/relatorios', [SuperAdminController::class, 'relatorios'])->name('relatorios');

    // Planos de Assinatura
    Route::get('/planos', [SuperAdminController::class, 'planos'])->name('planos');
    Route::get('/planos/{slug}/editar', [SuperAdminController::class, 'planoEditar'])->name('planos.editar');
    Route::put('/planos/{slug}', [SuperAdminController::class, 'planoAtualizar'])->name('planos.atualizar');
});
