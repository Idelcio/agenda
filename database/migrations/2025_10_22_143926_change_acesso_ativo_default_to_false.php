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
            // Altera o default de acesso_ativo para false
            // Novos usuários precisam ter assinatura ativa ou acesso liberado
            $table->boolean('acesso_ativo')->default(false)->change();
        });

        // IMPORTANTE: Usuários que já têm acesso_liberado_ate futuro ou são super_admin mantém acesso
        // Os demais novos usuários precisarão ter assinatura ativa
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Volta ao default anterior
            $table->boolean('acesso_ativo')->default(true)->change();
        });
    }
};
