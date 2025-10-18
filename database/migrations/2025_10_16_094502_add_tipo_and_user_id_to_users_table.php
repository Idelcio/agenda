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
            // Adiciona 'tipo' apenas se não existir
            if (!Schema::hasColumn('users', 'tipo')) {
                $table->string('tipo')->default('empresa')->after('email');
            }

            // Adiciona 'user_id' apenas se não existir
            if (!Schema::hasColumn('users', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('tipo')->constrained('users')->onDelete('cascade');
            }

            // Adiciona 'whatsapp_number' apenas se não existir
            if (!Schema::hasColumn('users', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable()->after('user_id');
            }

            // Adiciona 'is_admin' apenas se não existir
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('whatsapp_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'user_id', 'whatsapp_number', 'is_admin']);
        });
    }
};
