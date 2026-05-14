<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SQLite does not support ALTER COLUMN on CHECK constraints.
 * We use PRAGMA foreign_keys=OFF + raw DDL to recreate the constraint
 * with the two new status values: 'awaiting_verification' and 'verified'.
 *
 * For MySQL/Postgres this migration modifies the column definition directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->upgradeSQLite();
        } else {
            // MySQL / PostgreSQL — just change the column type
            DB::statement("
                ALTER TABLE complaints
                MODIFY COLUMN status ENUM(
                    'pending','under_review','in_progress',
                    'awaiting_verification','verified','rejected'
                ) NOT NULL DEFAULT 'pending'
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->downgradeSQLite();
        } else {
            DB::statement("
                ALTER TABLE complaints
                MODIFY COLUMN status ENUM(
                    'pending','under_review','in_progress','resolved','rejected'
                ) NOT NULL DEFAULT 'pending'
            ");
        }
    }

    // -------------------------------------------------------------------------
    // SQLite helpers
    // -------------------------------------------------------------------------

    private function upgradeSQLite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('PRAGMA legacy_alter_table = ON');

        DB::transaction(function () {
            // 1. Drop old CHECK-constrained column and add a plain TEXT one
            //    SQLite doesn't let us DROP COLUMN with a constraint in one step,
            //    so we rename → recreate the whole table approach.
            DB::statement('ALTER TABLE complaints RENAME TO _complaints_old');

            DB::statement("
                CREATE TABLE complaints (
                    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
                    complaint_number    TEXT    NOT NULL UNIQUE,
                    user_id             INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                    category_id         INTEGER NOT NULL REFERENCES categories(id) ON DELETE RESTRICT,
                    assigned_to         INTEGER          REFERENCES users(id)      ON DELETE SET NULL,
                    title               TEXT    NOT NULL,
                    description         TEXT    NOT NULL,
                    latitude            NUMERIC(10,7) NOT NULL,
                    longitude           NUMERIC(10,7) NOT NULL,
                    location            TEXT    NOT NULL,
                    severity            TEXT    NOT NULL DEFAULT 'medium'
                                        CHECK (severity IN ('low','medium','high','emergency')),
                    status              TEXT    NOT NULL DEFAULT 'pending'
                                        CHECK (status IN (
                                            'pending','under_review','in_progress',
                                            'awaiting_verification','verified','rejected'
                                        )),
                    votes_count         INTEGER NOT NULL DEFAULT 0,
                    views_count         INTEGER NOT NULL DEFAULT 0,
                    is_anonymous        INTEGER NOT NULL DEFAULT 0,
                    is_duplicate        INTEGER NOT NULL DEFAULT 0,
                    duplicate_of        INTEGER          REFERENCES complaints(id) ON DELETE SET NULL,
                    rejection_reason    TEXT,
                    estimated_completion TEXT,
                    resolved_at         TEXT,
                    created_at          TEXT,
                    updated_at          TEXT
                )
            ");

            // Copy all existing rows
            DB::statement("INSERT INTO complaints SELECT * FROM _complaints_old");

            // Recreate indexes
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_status_index     ON complaints (status)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_severity_index   ON complaints (severity)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_category_id_index ON complaints (category_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_assigned_to_index ON complaints (assigned_to)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_lat_lng_index    ON complaints (latitude, longitude)');

            DB::statement('DROP TABLE _complaints_old');
        });

        DB::statement('PRAGMA legacy_alter_table = OFF');
        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function downgradeSQLite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('PRAGMA legacy_alter_table = ON');

        DB::transaction(function () {
            DB::statement('ALTER TABLE complaints RENAME TO _complaints_old');

            DB::statement("
                CREATE TABLE complaints (
                    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
                    complaint_number    TEXT    NOT NULL UNIQUE,
                    user_id             INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                    category_id         INTEGER NOT NULL REFERENCES categories(id) ON DELETE RESTRICT,
                    assigned_to         INTEGER          REFERENCES users(id)      ON DELETE SET NULL,
                    title               TEXT    NOT NULL,
                    description         TEXT    NOT NULL,
                    latitude            NUMERIC(10,7) NOT NULL,
                    longitude           NUMERIC(10,7) NOT NULL,
                    location            TEXT    NOT NULL,
                    severity            TEXT    NOT NULL DEFAULT 'medium'
                                        CHECK (severity IN ('low','medium','high','emergency')),
                    status              TEXT    NOT NULL DEFAULT 'pending'
                                        CHECK (status IN (
                                            'pending','under_review','in_progress','resolved','rejected'
                                        )),
                    votes_count         INTEGER NOT NULL DEFAULT 0,
                    views_count         INTEGER NOT NULL DEFAULT 0,
                    is_anonymous        INTEGER NOT NULL DEFAULT 0,
                    is_duplicate        INTEGER NOT NULL DEFAULT 0,
                    duplicate_of        INTEGER          REFERENCES complaints(id) ON DELETE SET NULL,
                    rejection_reason    TEXT,
                    estimated_completion TEXT,
                    resolved_at         TEXT,
                    created_at          TEXT,
                    updated_at          TEXT
                )
            ");

            DB::statement("INSERT INTO complaints SELECT * FROM _complaints_old");

            DB::statement('CREATE INDEX IF NOT EXISTS complaints_status_index     ON complaints (status)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_severity_index   ON complaints (severity)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_category_id_index ON complaints (category_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_assigned_to_index ON complaints (assigned_to)');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_lat_lng_index    ON complaints (latitude, longitude)');

            DB::statement('DROP TABLE _complaints_old');
        });

        DB::statement('PRAGMA legacy_alter_table = OFF');
        DB::statement('PRAGMA foreign_keys = ON');
    }
};
