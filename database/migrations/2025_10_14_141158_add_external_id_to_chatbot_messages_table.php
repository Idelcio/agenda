<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table) {
            // 🔹 Adiciona coluna externa única (para evitar mensagens duplicadas)
            if (!Schema::hasColumn('chatbot_messages', 'external_id')) {
                $table->string('external_id')->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chatbot_messages', 'external_id')) {
                $table->dropUnique(['external_id']);
                $table->dropColumn('external_id');
            }
        });
    }
};
