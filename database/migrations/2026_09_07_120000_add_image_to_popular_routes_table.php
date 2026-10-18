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
        Schema::table('popular_routes', function (Blueprint $table) {
            // Optional — a route with no image just shows the plain text
            // card it always has (see route-card.blade.php); nothing forces
            // every route to have a photo.
            $table->string('image')->nullable()->after('original_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popular_routes', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
