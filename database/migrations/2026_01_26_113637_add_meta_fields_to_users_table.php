<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Definição do driver (Gateway atual vs Meta Cloud)
            // 'gateway' = API Brasil (QR Code)
            // 'meta' = WhatsApp Cloud API (Oficial)
            $table->string('whatsapp_driver')->default('gateway')->after('whatsapp_number');

            // Credenciais do Meta
            $table->string('meta_business_id')->nullable()->after('whatsapp_driver');
            $table->string('meta_phone_id')->nullable()->after('meta_business_id');
            $table->text('meta_access_token')->nullable()->after('meta_phone_id');

            // Controle de cotas (para o modelo híbrido de cobrança)
            $table->integer('quota_limit')->default(0)->after('meta_access_token')->comment('Limite de mensagens no plano Meta');
            $table->integer('quota_usage')->default(0)->after('quota_limit')->comment('Uso atual de mensagens no ciclo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_driver',
                'meta_business_id',
                'meta_phone_id',
                'meta_access_token',
                'quota_limit',
                'quota_usage',
            ]);
        });
    }
};
