<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            // Comma-separated inbox(es) that receive a copy of every admin
            // booking notification — admin login emails aren't necessarily
            // real, monitored mailboxes.
            $table->string('admin_notification_email', 500)->nullable()->after('from_name');
        });
    }

    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->dropColumn('admin_notification_email');
        });
    }
};
