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
        Schema::create('popular_routes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['airport', 'city', 'intercity'])->default('city');
            $table->string('pickup');
            $table->string('dropoff');
            $table->decimal('distance', 8, 2)->nullable();
            $table->string('distance_unit', 3)->default('km');
            $table->decimal('estimated_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popular_routes');
    }
};
