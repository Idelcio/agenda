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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('tipo_mensagem', 20)->default('compromisso')->after('whatsapp_mensagem');
            // Tipos: 'compromisso' (com botões de confirmação) ou 'aviso' (apenas informativo)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('tipo_mensagem');
        });
    }
};
