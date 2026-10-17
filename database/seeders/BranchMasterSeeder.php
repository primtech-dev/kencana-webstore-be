<?php

namespace Database\Seeders;

use App\Models\Branch;
use Database\Seeders\Concerns\ResyncsPgsqlSequences;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menyamakan data cabang dengan master (database/data/branches_master.php).
 *
 *  - Cabang dicocokkan berdasarkan KODE (termasuk yang sudah soft-delete -> dipulihkan),
 *    lalu berdasarkan NAMA (tanpa beda huruf besar/kecil); selain itu dibuat baru.
 *  - Cabang yang TIDAK ada di master dihapus (soft delete) bila tidak punya data terkait.
 *    Bila masih punya user / order / penerimaan stok / stok, cabang tidak dihapus,
 *    hanya dinonaktifkan, dan dilaporkan agar ditangani manual.
 *  - Idempotent: aman dijalankan berulang.
 *
 * Dry-run (tidak menyimpan apa pun, hanya laporan):
 *   SEED_DRY_RUN=1 php artisan db:seed --class=BranchMasterSeeder
 */
class BranchMasterSeeder extends Seeder
{
    use ResyncsPgsqlSequences;

    public function run(): void
    {
        $this->resyncPgsqlSequences();

        $dry = filter_var(env('SEED_DRY_RUN', false), FILTER_VALIDATE_BOOL);
        $master = require database_path('data/branches_master.php');

        $report = [];

        DB::beginTransaction();
        try {
            $keepIds = [];

            foreach ($master as $row) {
                [$branch, $how] = $this->match($row);

                $attrs = $row + ['is_active' => true];

                if ($branch === null) {
                    $branch = Branch::create($attrs);
                    $action = 'DIBUAT';
                } else {
                    if ($branch->trashed()) {
                        $branch->restore();
                    }
                    $branch->fill($attrs)->save();
                    $action = $how === 'code' ? 'DIPERBARUI' : 'DIPERBARUI (cocok nama)';
                }

                $keepIds[] = $branch->id;
                $report[] = [$action, $branch->id, $row['code'], $row['name']];
            }

            // Cabang di luar master
            $extras = Branch::query()->whereNotIn('id', $keepIds)->orderBy('id')->get();
            foreach ($extras as $extra) {
                $blockers = $this->blockers($extra);

                if ($blockers) {
                    $extra->update(['is_active' => false]);
                    $report[] = ['DITAHAN (nonaktif): ' . implode(', ', $blockers), $extra->id, $extra->code, $extra->name];
                    continue;
                }

                $extra->inventories()->delete();
                $extra->delete();
                $report[] = ['DIHAPUS', $extra->id, $extra->code, $extra->name];
            }

            $dry ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->command?->info($dry ? 'DRY-RUN: tidak ada perubahan yang disimpan.' : 'Data cabang disinkronkan dengan master.');
        $this->command?->table(['Aksi', 'ID', 'Kode', 'Nama'], $report);
    }

    /** @return array{0: ?Branch, 1: ?string} */
    private function match(array $row): array
    {
        $byCode = Branch::withTrashed()->where('code', $row['code'])->first();
        if ($byCode) {
            return [$byCode, 'code'];
        }

        $byName = Branch::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($row['name'])])->first();
        if ($byName) {
            return [$byName, 'name'];
        }

        return [null, null];
    }

    /** @return list<string> */
    private function blockers(Branch $branch): array
    {
        $blockers = [];
        if ($branch->users()->exists()) {
            $blockers[] = 'masih ada user';
        }
        if ($branch->orders()->withTrashed()->exists()) {
            $blockers[] = 'ada riwayat order';
        }
        if ($branch->stockReceipts()->withTrashed()->exists()) {
            $blockers[] = 'ada penerimaan stok';
        }
        if ($branch->inventories()->where('on_hand', '>', 0)->exists()) {
            $blockers[] = 'masih ada stok';
        }

        return $blockers;
    }
}
