<?php

namespace App\Providers;

use App\Models\Inventory;
use App\Observers\InventoryObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
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
        // Register route middleware alias for restricting staff access
        Route::aliasMiddleware('restrict.staff', \App\Http\Middleware\RestrictStaffAccess::class);
        Route::aliasMiddleware('role', \App\Http\Middleware\CheckRole::class);

        Inventory::observe(InventoryObserver::class);
    }
}
