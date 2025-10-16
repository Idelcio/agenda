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
            $table->string('tipo')->default('empresa')->after('email'); // empresa ou cliente
            $table->foreignId('user_id')->nullable()->after('tipo')->constrained('users')->onDelete('cascade'); // empresa pai
            $table->string('whatsapp_number')->nullable()->after('user_id');
            $table->boolean('is_admin')->default(false)->after('whatsapp_number');
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
