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
        Schema::table('pages', function (Blueprint $table) {
            $table->string('og_image')->nullable()->after('meta_description');
            $table->string('canonical_override')->nullable()->after('og_image');

            // Default true so every existing page keeps being indexed
            // exactly as it is today once this migration runs.
            $table->boolean('robots_index')->default(true)->after('canonical_override');
            $table->boolean('robots_follow')->default(true)->after('robots_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['og_image', 'canonical_override', 'robots_index', 'robots_follow']);
        });
    }
};
