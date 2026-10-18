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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('ride_started_at')->nullable()->after('status');
            $table->timestamp('estimated_arrival_at')->nullable()->after('ride_started_at');
            $table->decimal('dispatch_distance_km', 8, 2)->nullable()->after('estimated_arrival_at');
            $table->unsignedInteger('dispatch_duration_minutes')->nullable()->after('dispatch_distance_km');
            $table->decimal('dispatch_lat', 10, 7)->nullable()->after('dispatch_duration_minutes');
            $table->decimal('dispatch_lng', 10, 7)->nullable()->after('dispatch_lat');
            $table->timestamp('dispatch_calculated_at')->nullable()->after('dispatch_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'ride_started_at',
                'estimated_arrival_at',
                'dispatch_distance_km',
                'dispatch_duration_minutes',
                'dispatch_lat',
                'dispatch_lng',
                'dispatch_calculated_at',
            ]);
        });
    }
};
