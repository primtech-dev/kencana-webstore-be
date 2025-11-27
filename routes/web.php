<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTE AUTENTIKASI (PUBLIC)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    // ROOT → DASHBOARD
    Route::get('/', function () {
        return view('dashboard.dashboard');
    })->name('dashboard');


});

Route::middleware(['auth'])->name('branches.')->prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index'])
            ->name('index')
            ->middleware('permission:branches.view');

        Route::get('/create', [BranchController::class, 'create'])
            ->name('create')
            ->middleware('permission:branches.create');

        Route::post('/', [BranchController::class, 'store'])
            ->name('store')
            ->middleware('permission:branches.create');

        Route::get('/{id}/edit', [BranchController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:branches.update');

        Route::put('/{id}', [BranchController::class, 'update'])
            ->name('update')
            ->middleware('permission:branches.update');

        Route::delete('/{id}', [BranchController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:branches.delete');

        Route::get('/{id}', [BranchController::class, 'show'])
            ->name('show')
            ->middleware('permission:branches.view');
});

Route::middleware(['auth'])->name('categories.')->prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index')->middleware('permission:categories.view');
    Route::get('/create', [CategoryController::class, 'create'])->name('create')->middleware('permission:categories.create');
    Route::post('/', [CategoryController::class, 'store'])->name('store')->middleware('permission:categories.create');
    Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('edit')->middleware('permission:categories.update');
    Route::put('/{id}', [CategoryController::class, 'update'])->name('update')->middleware('permission:categories.update');
    Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy')->middleware('permission:categories.delete');
    Route::get('/{id}', [CategoryController::class, 'show'])->name('show')->middleware('permission:categories.view');
});

/*
|--------------------------------------------------------------------------
| FALLBACK 404
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
