<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Atualiza nomes de planos em português para inglês na tabela users
        DB::table('users')
            ->where('plano', 'mensal')
            ->update(['plano' => 'monthly']);

        DB::table('users')
            ->where('plano', 'trimestral')
            ->update(['plano' => 'quarterly']);

        DB::table('users')
            ->where('plano', 'semestral')
            ->update(['plano' => 'semiannual']);

        DB::table('users')
            ->where('plano', 'anual')
            ->update(['plano' => 'annual']);

        // Atualiza nomes de planos na tabela subscriptions se existir
        if (Schema::hasTable('subscriptions')) {
            DB::table('subscriptions')
                ->where('plan_type', 'mensal')
                ->update(['plan_type' => 'monthly']);

            DB::table('subscriptions')
                ->where('plan_type', 'trimestral')
                ->update(['plan_type' => 'quarterly']);

            DB::table('subscriptions')
                ->where('plan_type', 'semestral')
                ->update(['plan_type' => 'semiannual']);

            DB::table('subscriptions')
                ->where('plan_type', 'anual')
                ->update(['plan_type' => 'annual']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverte de inglês para português na tabela users
        DB::table('users')
            ->where('plano', 'monthly')
            ->update(['plano' => 'mensal']);

        DB::table('users')
            ->where('plano', 'quarterly')
            ->update(['plano' => 'trimestral']);

        DB::table('users')
            ->where('plano', 'semiannual')
            ->update(['plano' => 'semestral']);

        DB::table('users')
            ->where('plano', 'annual')
            ->update(['plano' => 'anual']);

        // Reverte na tabela subscriptions se existir
        if (Schema::hasTable('subscriptions')) {
            DB::table('subscriptions')
                ->where('plan_type', 'monthly')
                ->update(['plan_type' => 'mensal']);

            DB::table('subscriptions')
                ->where('plan_type', 'quarterly')
                ->update(['plan_type' => 'trimestral']);

            DB::table('subscriptions')
                ->where('plan_type', 'semiannual')
                ->update(['plan_type' => 'semestral']);

            DB::table('subscriptions')
                ->where('plan_type', 'annual')
                ->update(['plan_type' => 'anual']);
        }
    }
};
