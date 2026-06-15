<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;


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
        // Na produkcji (za proxy HTTPS Azure) wymuszamy generowanie linkow https,
        // zeby przegladarka nie blokowala CSS/JS jako "mixed content".
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

    }
}
