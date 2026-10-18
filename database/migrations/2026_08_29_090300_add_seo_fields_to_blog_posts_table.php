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
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('canonical_override')->nullable()->after('custom_schema');
            $table->boolean('robots_index')->default(true)->after('canonical_override');
            $table->boolean('robots_follow')->default(true)->after('robots_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['canonical_override', 'robots_index', 'robots_follow']);
        });
    }
};
