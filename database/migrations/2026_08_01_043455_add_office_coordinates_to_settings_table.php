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
        Schema::table('settings', function (Blueprint $table) {
            // Geocoded once from `address` and cached here by
            // OfficeLocationService — lives alongside address now that
            // Settings is the single source of truth for contact details
            // (previously cached on the Contact page's PageSection, which
            // duplicated address/phone/email/whatsapp with Settings).
            $table->decimal('office_lat', 10, 7)->nullable()->after('address');
            $table->decimal('office_lng', 10, 7)->nullable()->after('office_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['office_lat', 'office_lng']);
        });
    }
};
