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
        Schema::table('areas', function (Blueprint $table) {
            // `description` remains the short page-body intro shown under
            // the H1 — these are the dedicated <title>/<meta description>
            // fields an area page didn't have before, so admins no longer
            // have to rely on the generic "Reliable taxi service in {area}"
            // fallback for every single area.
            $table->string('meta_title')->nullable()->after('description');
            $table->string('meta_description', 500)->nullable()->after('meta_title');
            $table->string('og_image')->nullable()->after('meta_description');
            $table->string('canonical_override')->nullable()->after('og_image');
            $table->boolean('robots_index')->default(true)->after('canonical_override');
            $table->boolean('robots_follow')->default(true)->after('robots_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'og_image', 'canonical_override', 'robots_index', 'robots_follow']);
        });
    }
};
