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
        Schema::table('settings', function (Blueprint $table) {
            // Left blank, /robots.txt keeps generating its current sensible
            // default (see App\Http\Controllers\SitemapController::robots())
            // — this only takes over when an admin explicitly fills it in.
            $table->text('custom_robots_txt')->nullable()->after('default_robots_follow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('custom_robots_txt');
        });
    }
};
