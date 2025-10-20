<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Altera a coluna plano para aceitar os novos valores em inglês
        DB::statement("ALTER TABLE users MODIFY COLUMN plano ENUM('trial', 'monthly', 'quarterly', 'semiannual', 'annual', 'mensal', 'trimestral', 'semestral', 'anual') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverte para apenas valores em português
        DB::statement("ALTER TABLE users MODIFY COLUMN plano ENUM('trial', 'mensal', 'trimestral', 'semestral', 'anual') NULL");
    }
};
