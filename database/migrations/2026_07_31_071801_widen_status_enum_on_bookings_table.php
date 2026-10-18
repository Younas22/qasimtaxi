<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `bookings.status` is a native MySQL ENUM, not a plain string — adding
 * 'in_progress' to Booking::STATUSES alone isn't enough, the column itself
 * has to accept the new value or inserts/updates get silently truncated
 * (MySQL's default non-strict-enum behaviour) or rejected outright under
 * strict mode.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending','confirmed','assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending','confirmed','assigned','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
