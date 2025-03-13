<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\PurchaseRequest;
use App\Models\purchase_requests;

class EloquentObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        purchase_requests::observe(PurchaseRequest::class);
    }
}
