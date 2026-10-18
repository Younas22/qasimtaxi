<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_category_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('label')->nullable();

            $table->decimal('base_fare', 10, 2)->default(0);
            $table->decimal('km_fare', 10, 2)->default(0);
            $table->decimal('hour_fare', 10, 2)->default(0);

            $table->decimal('waiting_charge_per_minute', 10, 2)->default(0);
            $table->unsignedInteger('free_waiting_minutes')->default(0);

            $table->decimal('night_charge', 10, 2)->default(0);
            $table->time('night_start_time')->default('22:00:00');
            $table->time('night_end_time')->default('06:00:00');

            $table->decimal('weekend_charge', 10, 2)->default(0);
            $table->json('weekend_days')->nullable();

            $table->decimal('toll_charge', 10, 2)->default(0);
            $table->decimal('airport_surcharge', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
