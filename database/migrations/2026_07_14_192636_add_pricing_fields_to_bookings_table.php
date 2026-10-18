<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('waiting_minutes')->default(0)->after('luggage');
            $table->boolean('has_toll')->default(false)->after('waiting_minutes');
            $table->json('fare_breakdown')->nullable()->after('fare_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['waiting_minutes', 'has_toll', 'fare_breakdown']);
        });
    }
};
