<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->unique();
            $table->string('label');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('admin_panel_enabled')->default(true);

            // Future-ready channels — columns and admin UI exist now, but no
            // SMS/push provider is wired up yet, so these stay off and the
            // toggles are disabled in the UI until a provider is integrated.
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
