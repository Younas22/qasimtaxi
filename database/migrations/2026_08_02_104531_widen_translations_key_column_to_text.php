<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Long page-section content (rich-text bodies) is translated the same
     * way as headings — the literal English string is the lookup key — but
     * VARCHAR(255) is too short for multi-paragraph body text. Widen the
     * column and rebuild the unique index with an explicit prefix length,
     * since MySQL requires one for TEXT columns in an index. Raw SQL here
     * (not Schema::table()->change()) to avoid a doctrine/dbal dependency
     * that isn't installed in this project.
     */
    public function up(): void
    {
        // The composite unique index is the only one covering language_id,
        // so MySQL's FK constraint depends on it — add a plain index on
        // language_id first so the FK has something else to stand on.
        DB::statement('ALTER TABLE translations ADD INDEX translations_language_id_index (language_id)');
        DB::statement('ALTER TABLE translations DROP INDEX translations_language_group_key_unique');
        DB::statement('ALTER TABLE translations MODIFY `key` TEXT NOT NULL');
        DB::statement('ALTER TABLE translations ADD UNIQUE translations_language_group_key_unique (language_id, `group`, `key`(500))');
        DB::statement('ALTER TABLE translations DROP INDEX translations_language_id_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE translations ADD INDEX translations_language_id_index (language_id)');
        DB::statement('ALTER TABLE translations DROP INDEX translations_language_group_key_unique');
        DB::statement('ALTER TABLE translations MODIFY `key` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE translations ADD UNIQUE translations_language_group_key_unique (language_id, `group`, `key`)');
        DB::statement('ALTER TABLE translations DROP INDEX translations_language_id_index');
    }
};
