<?php

use App\Http\Controllers\ContentManagement\ArticleController;
use App\Http\Controllers\ContentManagement\FaqController;
use App\Http\Controllers\ContentManagement\TagController;
use App\Http\Controllers\ContentManagement\TestimonialController;
use App\Http\Controllers\ContentManagement\ProductController;
use App\Http\Controllers\CreditSimulation\CreditSimulationController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Customers\SubmissionController;
use App\Http\Controllers\Frontend\LandingController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/getTenors/{loanAmountId}', [LandingController::class, 'getTenors'])->name('landing.get-tenors');
Route::post('/submission', [LandingController::class, 'storeSubmission'])->name('landing.store-submission');
Route::get('/tentang-kami', [LandingController::class, 'aboutUs'])->name('landing.about-us');
Route::group(['prefix' => 'produk'], function () {
    Route::get('/', [LandingController::class, 'product'])->name('landing.product');
    Route::get('/{slug}', [LandingController::class, 'showProduct'])->name('landing.product-show');
});
Route::group(['prefix' => 'berita'], function () {
    Route::get('/', [NewsController::class, 'index'])->name('news.index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('news.show');
    Route::get('/tag/{slug}', [NewsController::class, 'showTag'])->name('news.show-tag');
});
Route::get('/kontak', [LandingController::class, 'contact'])->name('landing.contact');

Route::group(['prefix' => 'cms'], function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('articles.index');
        }
        return view('auth.login');
    });
    Route::middleware(['auth'])->group(function () {
        Route::get('dashboard', function () {
            return view('dashboard.dashboard');
        })->name('dashboard');

        Route::group(['prefix' => 'content-management'], function () {
            Route::group(['prefix' => 'articles'], function () {
                Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
                Route::get('/create', [ArticleController::class, 'create'])->name('articles.create');
                Route::post('/', [ArticleController::class, 'store'])->name('articles.store');
                Route::get('/{id}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
                Route::put('/{id}', [ArticleController::class, 'update'])->name('articles.update');
                Route::delete('/{id}', [ArticleController::class, 'destroy'])->name('articles.destroy');
            });

            Route::group(['prefix' => 'products'], function () {
                Route::get('/', [ProductController::class, 'index'])->name('products.index');
                Route::get('/create', [ProductController::class, 'create'])->name('products.create');
                Route::post('/', [ProductController::class, 'store'])->name('products.store');
                Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
                Route::put('/{id}', [ProductController::class, 'update'])->name('products.update');
                Route::delete('/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
            });

            Route::group(['prefix' => 'faqs'], function () {
                Route::get('/', [FaqController::class, 'index'])->name('faqs.index');
                Route::post('/', [FaqController::class, 'store'])->name('faqs.store');
                Route::put('/{id}', [FaqController::class, 'update'])->name('faqs.update');
                Route::delete('/{id}', [FaqController::class, 'destroy'])->name('faqs.destroy');
            });

            Route::group(['prefix' => 'tags'], function () {
                Route::get('/', [TagController::class, 'index'])->name('tags.index');
                Route::post('/', [TagController::class, 'store'])->name('tags.store');
                Route::put('/{id}', [TagController::class, 'update'])->name('tags.update');
                Route::delete('/{id}', [TagController::class, 'destroy'])->name('tags.destroy');
            });

            Route::group(['prefix' => 'testimonials'], function () {
                Route::get('/', [TestimonialController::class, 'index'])->name('testimonials.index');
                Route::post('/', [TestimonialController::class, 'store'])->name('testimonials.store');
                Route::put('/{id}', [TestimonialController::class, 'update'])->name('testimonials.update');
                Route::delete('/{id}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
            });
        });

        Route::group(['prefix' => 'customers'], function () {
            Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
            Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
            Route::put('/{id}', [CustomerController::class, 'update'])->name('customers.update');
            Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
            Route::group(['prefix' => 'submissions'], function () {
                Route::get('/', [SubmissionController::class, 'index'])->name('submissions.index');
            });
        });

        Route::group(['prefix' => 'credit-simulations'], function () {
            Route::get('/', [CreditSimulationController::class, 'index'])->name('credit-simulation.index');
            Route::group(['prefix' => 'loan-amounts'], function () {
                Route::post('/', [CreditSimulationController::class, 'storeLoanAmount'])->name('credit-simulation.store-loan-amount');
                Route::put('/{id}', [CreditSimulationController::class, 'updateLoanAmount'])->name('credit-simulation.update-loan-amount');
                Route::delete('/{id}', [CreditSimulationController::class, 'destroyLoanAmount'])->name('credit-simulation.destroy-loan-amount');
            });
            Route::group(['prefix' => 'tenors'], function () {
                Route::post('/', [CreditSimulationController::class, 'storeTenor'])->name('credit-simulation.store-tenor');
                Route::put('/{id}', [CreditSimulationController::class, 'updateTenor'])->name('credit-simulation.update-tenor');
                Route::delete('/{id}', [CreditSimulationController::class, 'destroyTenor'])->name('credit-simulation.destroy-tenor');
            });
            Route::group(['prefix' => 'installments'], function () {
                Route::get('/{loanAmountId}', [CreditSimulationController::class, 'showInstallments'])->name('credit-simulation.show-installments');
                Route::post('/', [CreditSimulationController::class, 'bulkSaveInstallments'])->name('credit-simulation.bulk-save-installments');
            });
        });

        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        });
    });
});

require __DIR__.'/auth.php';

Route::fallback(function () {
    if (request()->is('cms/*')) {
        return response()->view('errors.404', [], 404);
    }

    return response()->view('errors.404-public', [], 404);
});
