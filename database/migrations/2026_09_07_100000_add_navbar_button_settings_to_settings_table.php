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
            // Defaults match the top navbar's behavior before these existed
            // (phone shown everywhere, its number as text only from the md
            // breakpoint up, Pricing button shown everywhere) — so nothing
            // changes on an existing site until an admin touches these.
            $table->boolean('navbar_phone_show_desktop')->default(true)->after('phone');
            $table->boolean('navbar_phone_show_mobile')->default(true)->after('navbar_phone_show_desktop');
            $table->boolean('navbar_phone_number_text_desktop')->default(true)->after('navbar_phone_show_mobile');
            $table->boolean('navbar_phone_number_text_mobile')->default(false)->after('navbar_phone_number_text_desktop');
            $table->boolean('navbar_price_show_desktop')->default(true)->after('navbar_phone_number_text_mobile');
            $table->boolean('navbar_price_show_mobile')->default(true)->after('navbar_price_show_desktop');
            $table->string('navbar_price_label')->nullable()->after('navbar_price_show_mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
};
