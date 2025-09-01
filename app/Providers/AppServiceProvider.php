<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

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
        Collection::macro('sd', function () {
            $mean = $this->avg();
            $variance = $this->map(fn($value) => pow($value - $mean, 2))->avg();
            return sqrt($variance);
        });
    }
}
