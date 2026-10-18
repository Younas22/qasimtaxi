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
        Schema::table('pricing_rules', function (Blueprint $table) {
            // Beyond this many total km, the whole trip bills at
            // long_distance_km_fare instead of km_fare (a lower per-km rate
            // for long-distance/intercity trips) — null disables tiering
            // and the rule behaves exactly as before.
            $table->decimal('long_distance_threshold_km', 8, 2)->nullable()->after('km_fare');
            $table->decimal('long_distance_km_fare', 8, 2)->nullable()->after('long_distance_threshold_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn(['long_distance_threshold_km', 'long_distance_km_fare']);
        });
    }
};
