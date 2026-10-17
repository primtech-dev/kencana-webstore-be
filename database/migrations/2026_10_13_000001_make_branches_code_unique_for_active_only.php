<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Kode cabang boleh dipakai ulang setelah cabang lama di-soft-delete.
        Schema::table('branches', function ($table) {
            $table->dropUnique('branches_code_unique');
        });

        DB::statement('CREATE UNIQUE INDEX branches_code_active_unique ON branches (code) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS branches_code_active_unique');

        Schema::table('branches', function ($table) {
            $table->unique('code');
        });
    }
};
