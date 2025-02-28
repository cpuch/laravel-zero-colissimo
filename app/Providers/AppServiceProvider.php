<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Saloon\Laravel\SaloonServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(SaloonServiceProvider::class);
        $this->app->register(ValidationServiceProvider::class);
        $this->app->register(TranslationServiceProvider::class);
    }
}
