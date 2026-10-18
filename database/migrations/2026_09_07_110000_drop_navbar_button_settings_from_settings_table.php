<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Superseded within the same day by a single shared button list on the
     * home page's Hero section (PageSection::getHeroButtonsAttribute) that
     * now drives both the hero and the top navbar together — see
     * components/header.blade.php. Keeping both systems around would just
     * invite them to drift out of sync, so these columns go.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'navbar_phone_show_desktop',
                'navbar_phone_show_mobile',
                'navbar_phone_number_text_desktop',
                'navbar_phone_number_text_mobile',
                'navbar_price_show_desktop',
                'navbar_price_show_mobile',
                'navbar_price_label',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('navbar_phone_show_desktop')->default(true)->after('phone');
            $table->boolean('navbar_phone_show_mobile')->default(true)->after('navbar_phone_show_desktop');
            $table->boolean('navbar_phone_number_text_desktop')->default(true)->after('navbar_phone_show_mobile');
            $table->boolean('navbar_phone_number_text_mobile')->default(false)->after('navbar_phone_number_text_desktop');
            $table->boolean('navbar_price_show_desktop')->default(true)->after('navbar_phone_number_text_mobile');
            $table->boolean('navbar_price_show_mobile')->default(true)->after('navbar_price_show_desktop');
            $table->string('navbar_price_label')->nullable()->after('navbar_price_show_mobile');
        });
    }
};
