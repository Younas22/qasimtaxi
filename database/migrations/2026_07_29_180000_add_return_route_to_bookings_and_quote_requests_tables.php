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
            $table->string('return_pickup_location')->nullable()->after('return_datetime');
            $table->decimal('return_pickup_lat', 10, 7)->nullable()->after('return_pickup_location');
            $table->decimal('return_pickup_lng', 10, 7)->nullable()->after('return_pickup_lat');
            $table->string('return_pickup_place_id')->nullable()->after('return_pickup_lng');
            $table->string('return_dropoff_location')->nullable()->after('return_pickup_place_id');
            $table->decimal('return_dropoff_lat', 10, 7)->nullable()->after('return_dropoff_location');
            $table->decimal('return_dropoff_lng', 10, 7)->nullable()->after('return_dropoff_lat');
            $table->string('return_dropoff_place_id')->nullable()->after('return_dropoff_lng');
            $table->json('return_stops')->nullable()->after('return_dropoff_place_id');
            $table->decimal('return_distance_km', 8, 2)->nullable()->after('return_stops');
            $table->unsignedInteger('return_duration_minutes')->nullable()->after('return_distance_km');
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('return_pickup_location')->nullable()->after('type');
            $table->decimal('return_pickup_lat', 10, 7)->nullable()->after('return_pickup_location');
            $table->decimal('return_pickup_lng', 10, 7)->nullable()->after('return_pickup_lat');
            $table->string('return_pickup_place_id')->nullable()->after('return_pickup_lng');
            $table->string('return_dropoff_location')->nullable()->after('return_pickup_place_id');
            $table->decimal('return_dropoff_lat', 10, 7)->nullable()->after('return_dropoff_location');
            $table->decimal('return_dropoff_lng', 10, 7)->nullable()->after('return_dropoff_lat');
            $table->string('return_dropoff_place_id')->nullable()->after('return_dropoff_lng');
            $table->decimal('return_distance_km', 8, 2)->nullable()->after('return_dropoff_place_id');
            $table->unsignedInteger('return_duration_minutes')->nullable()->after('return_distance_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'return_pickup_location', 'return_pickup_lat', 'return_pickup_lng', 'return_pickup_place_id',
                'return_dropoff_location', 'return_dropoff_lat', 'return_dropoff_lng', 'return_dropoff_place_id',
                'return_stops', 'return_distance_km', 'return_duration_minutes',
            ]);
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn([
                'return_pickup_location', 'return_pickup_lat', 'return_pickup_lng', 'return_pickup_place_id',
                'return_dropoff_location', 'return_dropoff_lat', 'return_dropoff_lng', 'return_dropoff_place_id',
                'return_distance_km', 'return_duration_minutes',
            ]);
        });
    }
};
