<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Beberapa tabel di production pernah diisi lewat insert dengan id eksplisit
 * (mis. seed data awal), sehingga sequence id-nya tidak ikut maju dan
 * tertinggal di belakang MAX(id) yang sebenarnya. Ini menyebabkan insert
 * baru gagal dengan "duplicate key value violates unique constraint".
 *
 * Migration ini menyamakan setiap sequence id di schema public ke
 * GREATEST(nilai sequence saat ini, MAX(id) tabel) - hanya maju, tidak
 * pernah mundur - supaya aman dijalankan berkali-kali dan tidak berpengaruh
 * pada tabel yang sequence-nya memang sudah benar.
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
                cur_val bigint;
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
                    EXECUTE format('SELECT last_value FROM %s', seq_name) INTO cur_val;
                    EXECUTE format('SELECT COALESCE(MAX(%I), 0) FROM %I', r.column_name, r.table_name) INTO max_val;

                    IF max_val > cur_val THEN
                        PERFORM setval(seq_name, max_val);
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
