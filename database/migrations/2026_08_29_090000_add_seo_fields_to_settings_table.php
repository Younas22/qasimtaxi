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
            // Drives which schema.org LocalBusiness subtype (if any) the
            // dynamic schema builder emits — see App\Services\SchemaBuilder.
            $table->string('business_type')->default('other')->after('company_name');

            // "{page_title} | {business_name}" by default — see seo_title()
            // in app/helpers.php, which also guards against the business
            // name already being present in the page's own title.
            $table->string('seo_title_template')->default('{page_title} | {business_name}')->after('meta_keywords');

            // Fallback used whenever a page/area/post doesn't set its own
            // robots flags — new content stays indexable by default.
            $table->boolean('default_robots_index')->default(true)->after('seo_title_template');
            $table->boolean('default_robots_follow')->default(true)->after('default_robots_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'seo_title_template', 'default_robots_index', 'default_robots_follow']);
        });
    }
};
