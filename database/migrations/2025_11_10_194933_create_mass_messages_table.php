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
        Schema::create('mass_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Empresa que enviou
            $table->text('mensagem'); // Conteúdo da mensagem
            $table->integer('total_destinatarios')->default(0); // Total de clientes selecionados
            $table->integer('enviados')->default(0); // Quantos já foram enviados
            $table->integer('falhas')->default(0); // Quantos falharam
            $table->enum('status', ['pendente', 'processando', 'concluido', 'erro'])->default('pendente');
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('status');
        });

        // Tabela para registrar cada envio individual da mensagem em massa
        Schema::create('mass_message_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mass_message_id');
            $table->unsignedBigInteger('cliente_id'); // Cliente (user) que recebeu
            $table->string('telefone', 20); // Telefone do cliente
            $table->enum('status', ['pendente', 'enviado', 'erro'])->default('pendente');
            $table->text('erro_mensagem')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->foreign('mass_message_id')->references('id')->on('mass_messages')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('mass_message_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mass_message_items');
        Schema::dropIfExists('mass_messages');
    }
};
