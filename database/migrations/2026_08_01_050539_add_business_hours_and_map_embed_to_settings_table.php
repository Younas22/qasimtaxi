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
            // Per-day open/close times (Google-Business-Profile style),
            // replacing the old single free-text "hours" string that lived
            // on the Contact page section — moved here so every contact
            // detail lives in one place.
            $table->json('business_hours')->nullable()->after('whatsapp');
            $table->string('google_maps_embed_url', 2000)->nullable()->after('business_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['business_hours', 'google_maps_embed_url']);
        });
    }
};
