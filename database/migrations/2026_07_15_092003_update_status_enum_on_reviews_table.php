<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Widen the enum first so both the old and new values are valid while
        // existing rows are remapped, then narrow it down to the final set.
        DB::statement("ALTER TABLE reviews MODIFY status ENUM('pending', 'published', 'hidden', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");

        DB::table('reviews')->where('status', 'published')->update(['status' => 'approved']);
        DB::table('reviews')->where('status', 'hidden')->update(['status' => 'rejected']);

        DB::statement("ALTER TABLE reviews MODIFY status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reviews MODIFY status ENUM('pending', 'published', 'hidden', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");

        DB::table('reviews')->where('status', 'approved')->update(['status' => 'published']);
        DB::table('reviews')->where('status', 'rejected')->update(['status' => 'hidden']);

        DB::statement("ALTER TABLE reviews MODIFY status ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'pending'");
    }
};
