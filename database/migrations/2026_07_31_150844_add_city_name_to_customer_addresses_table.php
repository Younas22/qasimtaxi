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
        Schema::table('customer_addresses', function (Blueprint $table) {
            // Plain text, auto-filled from Google's returned locality —
            // deliberately not "city" (that name is already the belongsTo
            // relation to the admin-curated `cities` table) and not tied to
            // that limited city list at all, since matching against it was
            // the whole problem being fixed here.
            $table->string('city_name')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('city_name');
        });
    }
};
