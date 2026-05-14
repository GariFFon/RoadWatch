<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ADD COLUMN with FK via Blueprint in alter context.
        // Add columns with raw SQL instead.
        DB::statement('ALTER TABLE complaints ADD COLUMN engineer_rating INTEGER DEFAULT NULL');
        DB::statement('ALTER TABLE complaints ADD COLUMN engineer_rating_comment TEXT DEFAULT NULL');
        DB::statement('ALTER TABLE complaints ADD COLUMN rated_by INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE complaints ADD COLUMN rated_at TEXT DEFAULT NULL');
    }

    public function down(): void
    {
        // SQLite doesn't support DROP COLUMN directly in older versions.
        // For rollback we do nothing (columns remain but are nullable so harmless).
        // On a fresh migrate:fresh they'll be gone automatically.
    }
};

