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
            $table->decimal('pickup_lat', 10, 7)->nullable()->after('pickup_location');
            $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
            $table->string('pickup_place_id')->nullable()->after('pickup_lng');
            $table->decimal('dropoff_lat', 10, 7)->nullable()->after('dropoff_location');
            $table->decimal('dropoff_lng', 10, 7)->nullable()->after('dropoff_lat');
            $table->string('dropoff_place_id')->nullable()->after('dropoff_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['pickup_lat', 'pickup_lng', 'pickup_place_id', 'dropoff_lat', 'dropoff_lng', 'dropoff_place_id']);
        });
    }
};
