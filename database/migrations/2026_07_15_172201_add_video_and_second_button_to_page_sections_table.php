<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->string('video')->nullable()->after('image');
            $table->string('button_text_2')->nullable()->after('button_url');
            $table->string('button_url_2')->nullable()->after('button_text_2');
        });
    }

    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropColumn(['video', 'button_text_2', 'button_url_2']);
        });
    }
};
