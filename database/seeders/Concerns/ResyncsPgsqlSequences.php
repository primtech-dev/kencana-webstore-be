<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Memajukan sequence id (PostgreSQL) yang tertinggal dari MAX(id) tabelnya, mis. karena data
 * pernah diimpor dengan id eksplisit. Tanpa ini, insert baru gagal dengan
 * "duplicate key value violates unique constraint ..._pkey".
 *
 * Hanya memajukan sequence yang next-value efektifnya <= MAX(id); tidak pernah memundurkan.
 */
trait ResyncsPgsqlSequences
{
    protected function resyncPgsqlSequences(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            DO $$
            DECLARE
                r RECORD;
                seq_name text;
                cur_last bigint;
                cur_called boolean;
                effective_next bigint;
                max_val bigint;
            BEGIN
                FOR r IN
                    SELECT c.relname AS table_name, a.attname AS column_name
                    FROM pg_class c
                    JOIN pg_attribute a ON a.attrelid = c.oid
                    WHERE c.relkind = 'r'
                      AND c.relnamespace = 'public'::regnamespace
                      AND a.attnum > 0
                      AND NOT a.attisdropped
                      AND pg_get_serial_sequence(c.relname, a.attname) IS NOT NULL
                LOOP
                    seq_name := pg_get_serial_sequence(r.table_name, r.column_name);
                    EXECUTE format('SELECT last_value, is_called FROM %s', seq_name) INTO cur_last, cur_called;
                    effective_next := CASE WHEN cur_called THEN cur_last + 1 ELSE cur_last END;

                    EXECUTE format('SELECT COALESCE(MAX(%I), 0) FROM %I', r.column_name, r.table_name) INTO max_val;

                    IF effective_next <= max_val THEN
                        PERFORM setval(seq_name, max_val, true);
                    END IF;
                END LOOP;
            END $$;
        SQL);
    }
}
