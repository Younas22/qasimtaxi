<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('driver_auto_assignment_enabled');
            $table->text('whatsapp_message_template')->nullable()->after('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'whatsapp_message_template']);
        });
    }
};
