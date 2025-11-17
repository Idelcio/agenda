<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mass_messages', function (Blueprint $table) {
            $table->string('titulo')->nullable()->after('user_id');
            $table->timestamp('scheduled_for')->nullable()->after('concluido_em');
        });

        Schema::table('media_messages', function (Blueprint $table) {
            $table->string('titulo')->nullable()->after('user_id');
            $table->timestamp('scheduled_for')->nullable()->after('concluido_em');
        });

        DB::statement('UPDATE mass_messages SET scheduled_for = COALESCE(iniciado_em, created_at)');
        DB::statement('UPDATE media_messages SET scheduled_for = COALESCE(iniciado_em, created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_messages', function (Blueprint $table) {
            $table->dropColumn(['titulo', 'scheduled_for']);
        });

        Schema::table('mass_messages', function (Blueprint $table) {
            $table->dropColumn(['titulo', 'scheduled_for']);
        });
    }
};

