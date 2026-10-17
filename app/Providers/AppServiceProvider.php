<?php

namespace App\Providers;

use App\Interfaces\ContentManagement\ArticleInterface;
use App\Interfaces\ContentManagement\FaqInterface;
use App\Interfaces\ContentManagement\ProductInterface;
use App\Interfaces\ContentManagement\TagInterface;
use App\Interfaces\ContentManagement\TestimonialInterface;
use App\Interfaces\CreditSimulation\CreditSimulationInterface;
use App\Interfaces\Customers\CustomerInterface;
use App\Interfaces\Customers\SubmissionInterface;
use App\Repositories\ContentManagement\ArticleRepository;
use App\Repositories\ContentManagement\FaqRepository;
use App\Repositories\ContentManagement\ProductRepository;
use App\Repositories\ContentManagement\TagRepository;
use App\Repositories\ContentManagement\TestimonialRepository;
use App\Repositories\CreditSimulation\CreditSimulationRepository;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Customers\SubmissionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleInterface::class, ArticleRepository::class);
        $this->app->bind(FaqInterface::class, FaqRepository::class);
        $this->app->bind(TagInterface::class, TagRepository::class);
        $this->app->bind(TestimonialInterface::class, TestimonialRepository::class);
        $this->app->bind(ProductInterface::class, ProductRepository::class);
        $this->app->bind(CustomerInterface::class, CustomerRepository::class);
        $this->app->bind(SubmissionInterface::class, SubmissionRepository::class);
        $this->app->bind(CreditSimulationInterface::class, CreditSimulationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Carbon\Carbon::setLocale('id');

        // Superadmin selalu lolos semua permission/gate
        \Illuminate\Support\Facades\Gate::before(function ($user) {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() ? true : null;
        });

        // Manajemen role & permission hanya untuk superadmin (mencegah eskalasi hak oleh admin cabang)
        \Illuminate\Support\Facades\Gate::define('manage-access', fn ($user) => $user->isSuperAdmin());
    }
}
