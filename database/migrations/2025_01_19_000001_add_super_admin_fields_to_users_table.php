<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 🔹 Campo para identificar super admin
            $table->boolean('is_super_admin')->default(false);

            // 🔹 Controle de acesso por período
            $table->timestamp('acesso_liberado_ate')->nullable();
            $table->boolean('acesso_ativo')->default(true);

            // 🔹 Estatísticas de uso
            $table->integer('total_requisicoes')->default(0);
            $table->integer('requisicoes_mes_atual')->default(0);
            $table->date('ultimo_reset_requisicoes')->nullable();

            // 🔹 Plano contratado
            $table->enum('plano', ['trial', 'mensal', 'trimestral', 'semestral', 'anual'])->default('trial');
            $table->integer('limite_requisicoes_mes')->default(100);

            // 🔹 Informações de pagamento
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->timestamp('data_ultimo_pagamento')->nullable();

            // 🔹 Observações do admin
            $table->text('observacoes_admin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_super_admin',
                'acesso_liberado_ate',
                'acesso_ativo',
                'total_requisicoes',
                'requisicoes_mes_atual',
                'ultimo_reset_requisicoes',
                'plano',
                'limite_requisicoes_mes',
                'valor_pago',
                'data_ultimo_pagamento',
                'observacoes_admin',
            ]);
        });
    }
};
