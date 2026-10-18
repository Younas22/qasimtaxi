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
        Schema::table('drivers', function (Blueprint $table) {
            $table->decimal('current_lat', 10, 7)->nullable()->after('commission_rate');
            $table->decimal('current_lng', 10, 7)->nullable()->after('current_lat');
            $table->timestamp('location_updated_at')->nullable()->after('current_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['current_lat', 'current_lng', 'location_updated_at']);
        });
    }
};
