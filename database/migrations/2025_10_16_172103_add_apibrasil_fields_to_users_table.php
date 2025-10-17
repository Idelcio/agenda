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
            $table->string('apibrasil_device_token')->nullable()->after('whatsapp_number');
            $table->string('apibrasil_device_name')->nullable()->after('apibrasil_device_token');
            $table->string('apibrasil_device_id')->nullable()->after('apibrasil_device_name');
            $table->enum('apibrasil_qrcode_status', ['pending', 'connected', 'disconnected'])
                  ->default('pending')
                  ->after('apibrasil_device_id');
            $table->boolean('apibrasil_setup_completed')->default(false)->after('apibrasil_qrcode_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'apibrasil_device_token',
                'apibrasil_device_name',
                'apibrasil_device_id',
                'apibrasil_qrcode_status',
                'apibrasil_setup_completed'
            ]);
        });
    }
};
