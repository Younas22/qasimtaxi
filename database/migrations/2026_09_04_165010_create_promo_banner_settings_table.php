<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single-row settings table (same pattern as Setting/BookingSetting) for the
 * two self-promotional "this software is for sale" elements shown only on
 * the vendor's own sale/demo domain (see software-sale-modal.blade.php and
 * software-sale-sticky-banner.blade.php) — deliberately kept separate from
 * the main `settings` table since these fields are vendor-only and mean
 * nothing to an actual client's copy of this app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_banner_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('sale_modal_enabled')->default(true);
            $table->boolean('sticky_banner_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_banner_settings');
    }
};
