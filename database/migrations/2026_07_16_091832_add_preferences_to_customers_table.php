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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('locale')->nullable()->after('status');
            $table->string('currency')->nullable()->after('locale');
            $table->string('theme_mode')->nullable()->after('currency');
            $table->boolean('email_notifications_enabled')->default(true)->after('theme_mode');
            $table->boolean('push_notifications_enabled')->default(true)->after('email_notifications_enabled');
            $table->boolean('sms_notifications_enabled')->default(true)->after('push_notifications_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'locale',
                'currency',
                'theme_mode',
                'email_notifications_enabled',
                'push_notifications_enabled',
                'sms_notifications_enabled',
            ]);
        });
    }
};
