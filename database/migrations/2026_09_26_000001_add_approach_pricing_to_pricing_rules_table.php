<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->decimal('approach_free_km', 8, 2)->default(0)->after('extra_passenger_charge');
            $table->decimal('approach_km_fare', 8, 2)->default(0)->after('approach_free_km');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn(['approach_free_km', 'approach_km_fare']);
        });
    }
};
