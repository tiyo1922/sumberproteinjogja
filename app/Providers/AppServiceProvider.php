<?php

namespace App\Providers;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\KnowledgeRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentKnowledgeRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentReviewRepository;
use App\Repositories\Eloquent\EloquentSiteSettingRepository;
use App\Repositories\MockAnalyticsRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [
        CategoryRepositoryInterface::class => EloquentCategoryRepository::class,
        ProductRepositoryInterface::class => EloquentProductRepository::class,
        KnowledgeRepositoryInterface::class => EloquentKnowledgeRepository::class,
        ReviewRepositoryInterface::class => EloquentReviewRepository::class,
        SiteSettingRepositoryInterface::class => EloquentSiteSettingRepository::class,
        AnalyticsRepositoryInterface::class => MockAnalyticsRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
