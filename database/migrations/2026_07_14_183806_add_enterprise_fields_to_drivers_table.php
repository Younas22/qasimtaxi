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
        Schema::table('drivers', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('id')->constrained()->nullOnDelete();

            $table->text('address')->nullable()->after('phone');
            $table->string('passport_number')->nullable()->after('license_number');
            $table->string('national_id')->nullable()->after('passport_number');

            $table->decimal('commission_rate', 5, 2)->default(0)->after('status');
            $table->boolean('is_online')->default(false)->after('commission_rate');
            $table->boolean('is_available')->default(true)->after('is_online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_id');

            $table->dropColumn([
                'address',
                'passport_number',
                'national_id',
                'commission_rate',
                'is_online',
                'is_available',
            ]);
        });
    }
};
