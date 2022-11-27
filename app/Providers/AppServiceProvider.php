<?php

namespace App\Providers;

use App\Models\Sale;
use App\Models\Stock;
use App\Observers\SaleObserver;
use App\Observers\StockObserver;
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
        Sale::observe(SaleObserver::class);
        Stock::observe(StockObserver::class);
    }
}
