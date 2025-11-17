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
        Schema::create('media_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Empresa que enviou
            $table->text('mensagem')->nullable(); // Legenda opcional
            $table->string('arquivo_path'); // Caminho do arquivo (imagem ou PDF)
            $table->enum('tipo_arquivo', ['imagem', 'pdf']); // Tipo do arquivo
            $table->integer('total_destinatarios')->default(0);
            $table->integer('enviados')->default(0);
            $table->integer('falhas')->default(0);
            $table->enum('status', ['pendente', 'processando', 'concluido', 'erro'])->default('pendente');
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });

        Schema::create('media_message_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_message_id');
            $table->unsignedBigInteger('cliente_id');
            $table->string('telefone', 20);
            $table->enum('status', ['pendente', 'enviado', 'erro'])->default('pendente');
            $table->text('erro_mensagem')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->foreign('media_message_id')->references('id')->on('media_messages')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('media_message_id');
            $table->index('cliente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_message_items');
        Schema::dropIfExists('media_messages');
    }
};
