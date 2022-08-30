<?php

namespace App\Providers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Observers\PurchaseObserver;
use App\Observers\SaleObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Purchase::observe(PurchaseObserver::class);
        Sale::observe(SaleObserver::class);
    }
}
