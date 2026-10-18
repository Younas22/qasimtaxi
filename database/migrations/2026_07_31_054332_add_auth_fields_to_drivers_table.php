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
            $table->string('password')->nullable()->after('photo');
            $table->rememberToken()->after('password');
            $table->string('locale')->nullable()->after('remember_token');
            $table->string('theme_mode')->nullable()->after('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'locale', 'theme_mode']);
        });
    }
};
