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
            $table->text('meta_description')->nullable()->after('tagline');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('meta_keywords');
            $table->string('google_site_verification')->nullable()->after('og_image');
            $table->string('facebook_url')->nullable()->after('google_site_verification');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('twitter_url')->nullable()->after('instagram_url');
            $table->string('linkedin_url')->nullable()->after('twitter_url');
            $table->string('youtube_url')->nullable()->after('linkedin_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'meta_description', 'meta_keywords', 'og_image', 'google_site_verification',
                'facebook_url', 'instagram_url', 'twitter_url', 'linkedin_url', 'youtube_url',
            ]);
        });
    }
};
