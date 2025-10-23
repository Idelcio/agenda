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
            $table->text('observacoes')->nullable();
            $table->boolean('recorrente')->default(false);
            $table->enum('frequencia_recorrencia', ['semanal', 'quinzenal', 'mensal', 'anual'])->nullable();
            $table->date('data_fim_recorrencia')->nullable();
            $table->unsignedBigInteger('compromisso_pai_id')->nullable();

            // Adiciona índice para o compromisso pai
            $table->foreign('compromisso_pai_id')
                  ->references('id')
                  ->on('appointments')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['compromisso_pai_id']);
            $table->dropColumn(['observacoes', 'recorrente', 'frequencia_recorrencia', 'data_fim_recorrencia', 'compromisso_pai_id']);
        });
    }
};
