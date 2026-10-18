<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('manual_booking_enabled')->default(true);
            $table->boolean('website_booking_enabled')->default(true);
            $table->boolean('guest_booking_enabled')->default(true);
            $table->boolean('auto_confirmation_enabled')->default(false);
            $table->boolean('driver_auto_assignment_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_settings');
    }
};
