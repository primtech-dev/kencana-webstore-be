<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 2026_09_23_020950 gagal memperbaiki sequence yang is_called-nya
 * false (mis. bekas ALTER SEQUENCE ... RESTART WITH n) - kondisi ini bikin
 * last_value sequence sama dengan MAX(id) tabel, tapi nextval() berikutnya
 * tetap mengembalikan MAX(id) itu lagi (belum "dipakai"), sehingga guard
 * `max_val > cur_val` pada migration lama menganggap sequence sudah benar
 * padahal masih akan tabrakan.
 *
 * Migration ini menghitung next-value efektif dari tiap sequence
 * (last_value + 1 jika is_called, else last_value) dan hanya memperbaiki
 * yang next-value efektifnya <= MAX(id) tabel - jadi tetap aman dijalankan
 * berkali-kali dan tidak memundurkan sequence yang sudah benar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
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

    public function down(): void
    {
        // Tidak ada state yang berarti untuk di-rollback - sequence hanya dimajukan.
    }
};
