<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Pembersihan file sementara import (Excel/ZIP yang tidak jadi dikonfirmasi)
|--------------------------------------------------------------------------
*/
Artisan::command('imports:cleanup {--hours=24 : Hapus file yang lebih lama dari N jam}', function () {
    $cutoff = now()->subHours((int) $this->option('hours'))->getTimestamp();
    $deleted = 0;

    foreach (['app/private/tmp/import', 'app/private/tmp/stock-import', 'app/tmp-preview', 'app/tmp-import'] as $rel) {
        $dir = storage_path($rel);
        if (! is_dir($dir)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->getMTime() >= $cutoff) {
                continue;
            }
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
            $deleted++;
        }
    }

    $this->info("Selesai: {$deleted} item sementara dihapus.");
})->purpose('Hapus file sementara import yang sudah kedaluwarsa');

Schedule::command('imports:cleanup')->daily();
