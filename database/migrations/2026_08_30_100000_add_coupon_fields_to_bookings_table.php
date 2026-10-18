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
        Schema::table('bookings', function (Blueprint $table) {
            // Nullable + set null on delete: a coupon can be removed later
            // without breaking historical bookings that once used it — the
            // discount_amount already charged stays on the booking either way.
            $table->foreignId('coupon_id')->nullable()->after('vehicle_id')->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0)->after('fare_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('discount_amount');
        });
    }
};
