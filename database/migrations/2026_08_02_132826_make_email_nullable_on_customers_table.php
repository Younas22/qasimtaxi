<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Guest bookings can now be placed without an email address — the
     * unique index stays (MySQL allows multiple NULLs under a unique
     * constraint), only the NOT NULL restriction is dropped. Raw SQL, not
     * Schema::table()->change(), since doctrine/dbal isn't installed here.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE customers MODIFY `email` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE customers MODIFY `email` VARCHAR(255) NOT NULL');
    }
};
