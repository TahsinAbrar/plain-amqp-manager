<?php

namespace App\Library\PlainAmqpManager;

use Illuminate\Support\ServiceProvider;

class PlainAmqpManagerServiceProvider extends ServiceProvider
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
        $this->app->singleton(PlainAmqpManager::class, function () {
            return new PlainAmqpManager();
        });
    }
}