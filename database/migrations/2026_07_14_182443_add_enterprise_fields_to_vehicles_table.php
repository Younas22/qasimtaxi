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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('type');

            $table->foreignId('vehicle_category_id')->nullable()->after('id')->constrained()->nullOnDelete();

            $table->string('brand')->after('name');
            $table->string('model')->after('brand');
            $table->unsignedSmallInteger('year')->after('model');
            $table->unsignedTinyInteger('seats')->default(4)->after('year');
            $table->unsignedTinyInteger('luggage')->default(2)->after('seats');
            $table->enum('transmission', ['automatic', 'manual'])->default('automatic')->after('luggage');
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric'])->default('petrol')->after('transmission');

            $table->text('description')->nullable()->after('fuel_type');

            $table->decimal('base_fare', 10, 2)->default(0)->after('description');
            $table->decimal('price_per_km', 10, 2)->default(0)->after('base_fare');
            $table->decimal('price_per_hour', 10, 2)->default(0)->after('price_per_km');
            $table->decimal('airport_price', 10, 2)->default(0)->after('price_per_hour');
            $table->decimal('night_charges', 10, 2)->default(0)->after('airport_price');

            $table->boolean('has_wifi')->default(false)->after('night_charges');
            $table->boolean('has_water')->default(false)->after('has_wifi');
            $table->boolean('has_charger')->default(false)->after('has_water');
            $table->boolean('has_baby_seat')->default(false)->after('has_charger');
            $table->boolean('has_ac')->default(true)->after('has_baby_seat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_category_id');

            $table->dropColumn([
                'brand',
                'model',
                'year',
                'seats',
                'luggage',
                'transmission',
                'fuel_type',
                'description',
                'base_fare',
                'price_per_km',
                'price_per_hour',
                'airport_price',
                'night_charges',
                'has_wifi',
                'has_water',
                'has_charger',
                'has_baby_seat',
                'has_ac',
            ]);

            $table->string('type')->nullable();
        });
    }
};
