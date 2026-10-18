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
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('address_line');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('place_id')->nullable()->after('lng');
            $table->string('country')->nullable()->after('place_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'place_id', 'country']);
        });
    }
};
