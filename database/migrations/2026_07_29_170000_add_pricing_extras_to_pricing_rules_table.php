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
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->decimal('minimum_fare', 10, 2)->default(0)->after('service_fee');
            $table->decimal('included_km', 8, 2)->default(0)->after('minimum_fare');
            $table->decimal('included_hours', 8, 2)->default(0)->after('included_km');
            $table->unsignedInteger('included_passengers')->default(4)->after('included_hours');
            $table->decimal('extra_passenger_charge', 10, 2)->default(0)->after('included_passengers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn(['minimum_fare', 'included_km', 'included_hours', 'included_passengers', 'extra_passenger_charge']);
        });
    }
};
