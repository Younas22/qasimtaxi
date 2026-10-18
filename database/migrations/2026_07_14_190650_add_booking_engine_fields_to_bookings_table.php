<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('type', ['airport_transfer', 'one_way', 'round_trip', 'hourly', 'corporate'])
                ->default('one_way')->after('vehicle_id');

            $table->json('stops')->nullable()->after('dropoff_location');
            $table->dateTime('return_datetime')->nullable()->after('pickup_datetime');
            $table->unsignedTinyInteger('hours')->nullable()->after('return_datetime');
            $table->decimal('distance_km', 8, 2)->nullable()->after('hours');

            $table->unsignedTinyInteger('passengers')->default(1)->after('distance_km');
            $table->unsignedTinyInteger('luggage')->default(0)->after('passengers');
            $table->text('notes')->nullable()->after('luggage');
        });

        // MySQL enum changes require raw SQL rather than a fluent ->change(),
        // which would otherwise need doctrine/dbal as an extra dependency.
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending', 'confirmed', 'assigned', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'stops',
                'return_datetime',
                'hours',
                'distance_km',
                'passengers',
                'luggage',
                'notes',
            ]);
        });

        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending', 'confirmed', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
