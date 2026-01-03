<?php

namespace App\Providers;

use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\GroupRepositoryInterface;
use App\Repositories\ExpenseRepository;
use App\Repositories\GroupRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Repository bindings.
 * 
 * This provider binds repository interfaces to their concrete implementations,
 * enabling dependency injection and making the code testable.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     */
    public function register(): void
    {
        $this->app->bind(
            ExpenseRepositoryInterface::class,
            ExpenseRepository::class
        );

        $this->app->bind(
            GroupRepositoryInterface::class,
            GroupRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
