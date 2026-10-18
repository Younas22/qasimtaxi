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
        Schema::table('page_sections', function (Blueprint $table) {
            // Hero-only fields (like heading/subheading/button_text): a
            // small label above the headline, and a short, specific
            // differentiator line between the subheading and the CTA row
            // (e.g. "Fixed prices. No hidden fees.") — admin-entered, never
            // auto-filled, since a false claim is worse than no claim.
            $table->string('eyebrow')->nullable()->after('heading');
            $table->string('differentiator', 160)->nullable()->after('subheading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropColumn(['eyebrow', 'differentiator']);
        });
    }
};
