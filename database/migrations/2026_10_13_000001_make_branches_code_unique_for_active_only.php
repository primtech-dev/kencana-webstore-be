<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kode cabang boleh dipakai ulang setelah cabang lama di-soft-delete:
 * unique penuh pada branches.code diganti partial unique index (hanya baris aktif).
 *
 * Nama constraint/index unik lama TIDAK diasumsikan (di beberapa database berupa
 * constraint "branches_code_unique", di yang lain berupa index dengan nama berbeda),
 * jadi dicari lewat katalog Postgres. Aman dijalankan berulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $codeAttnum = DB::selectOne(
            "select attnum from pg_attribute where attrelid = 'branches'::regclass and attname = 'code' and not attisdropped"
        )->attnum;

        // 1) constraint unique murni pada (code)
        $constraints = DB::select(
            "select conname from pg_constraint
             where conrelid = 'branches'::regclass and contype = 'u' and conkey = ARRAY[?]::smallint[]",
            [$codeAttnum]
        );
        foreach ($constraints as $c) {
            DB::statement('alter table "branches" drop constraint "' . str_replace('"', '""', $c->conname) . '"');
        }

        // 2) unique index murni pada (code) yang bukan partial dan bukan milik constraint
        $indexes = DB::select(
            "select i.relname
             from pg_index x
             join pg_class i on i.oid = x.indexrelid
             where x.indrelid = 'branches'::regclass
               and x.indisunique and not x.indisprimary
               and x.indpred is null and x.indexprs is null
               and x.indnatts = 1 and x.indkey[0] = ?",
            [$codeAttnum]
        );
        foreach ($indexes as $i) {
            DB::statement('drop index if exists "' . str_replace('"', '""', $i->relname) . '"');
        }

        DB::statement('create unique index if not exists branches_code_active_unique on branches (code) where deleted_at is null');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('drop index if exists branches_code_active_unique');

        // Gagal bila ada kode ganda (mis. cabang aktif dan terhapus memakai kode sama): bersihkan dulu.
        DB::statement('create unique index if not exists branches_code_unique on branches (code)');
    }
};
