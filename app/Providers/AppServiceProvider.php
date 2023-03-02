<?php

namespace App\Providers;

use App\Models\TelegramUser;
use App\Observers\TelegramUserObserver;
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
        TelegramUser::observe(TelegramUserObserver::class);
    }
}
