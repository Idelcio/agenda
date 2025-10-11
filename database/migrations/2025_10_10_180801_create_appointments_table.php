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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->dateTime('inicio');
            $table->dateTime('fim')->nullable();
            $table->boolean('dia_inteiro')->default(false);
            $table->string('status', 20)->default('pendente');
            $table->boolean('notificar_whatsapp')->default(false);
            $table->string('whatsapp_numero', 30)->nullable();
            $table->text('whatsapp_mensagem')->nullable();
            $table->unsignedInteger('antecedencia_minutos')->nullable();
            $table->dateTime('lembrar_em')->nullable();
            $table->dateTime('lembrete_enviado_em')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'inicio']);
            $table->index(['status']);
            $table->index(['lembrar_em']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
