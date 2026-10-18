<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->decimal('mid_distance_threshold_km', 8, 2)->nullable()->after('km_fare');
            $table->decimal('mid_distance_km_fare', 8, 2)->nullable()->after('mid_distance_threshold_km');
            $table->decimal('very_long_distance_threshold_km', 8, 2)->nullable()->after('long_distance_km_fare');
            $table->decimal('very_long_distance_km_fare', 8, 2)->nullable()->after('very_long_distance_threshold_km');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn([
                'mid_distance_threshold_km',
                'mid_distance_km_fare',
                'very_long_distance_threshold_km',
                'very_long_distance_km_fare',
            ]);
        });
    }
};
