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
            // Null = base fare always applies (current/default behaviour).
            // Set = base fare only applies to trips at or under this
            // distance; longer trips skip it and bill purely per km.
            $table->decimal('base_fare_threshold_km', 8, 2)->nullable()->after('base_fare');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn('base_fare_threshold_km');
        });
    }
};
