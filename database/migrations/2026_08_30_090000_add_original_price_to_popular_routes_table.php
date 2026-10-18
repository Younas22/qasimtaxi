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
        Schema::table('popular_routes', function (Blueprint $table) {
            // Optional "was" price shown with a strikethrough next to
            // estimated_price (the current/discounted price) on the public
            // Popular Routes cards — see components/route-card.blade.php.
            // Left null, a route displays exactly as it did before.
            $table->decimal('original_price', 10, 2)->nullable()->after('estimated_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popular_routes', function (Blueprint $table) {
            $table->dropColumn('original_price');
        });
    }
};
