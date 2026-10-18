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
            // Shown on the public route card in place of the main photo
            // while it's hovered — optional, and only takes effect when the
            // main photo is also set (see route-card.blade.php).
            $table->string('hover_image')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popular_routes', function (Blueprint $table) {
            $table->dropColumn('hover_image');
        });
    }
};
