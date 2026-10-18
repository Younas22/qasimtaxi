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
            $table->string('primary_color', 7)->default('#c9a24b')->after('date_format');
            $table->string('secondary_color', 7)->default('#9ca3af')->after('primary_color');
            $table->string('accent_color', 7)->default('#e6c877')->after('secondary_color');
            $table->string('text_color', 7)->default('#f5f5f4')->after('accent_color');
            $table->string('background_color', 7)->default('#0a0a0a')->after('text_color');
            $table->string('theme_mode', 10)->default('dark')->after('background_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color',
                'secondary_color',
                'accent_color',
                'text_color',
                'background_color',
                'theme_mode',
            ]);
        });
    }
};
