<?php

namespace App\Providers;

use App\Services\AdaptiveMfaService;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('amfa', function ($app) {
            return new AdaptiveMfaService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Collection::macro('sd', function () {

            if ($this->count() === 0) {
                return 0;
            }

            $mean = $this->avg();
            $distance_sum = 0;
            $this->each(function ($value) use (&$distance_sum, $mean) {
                $distance_sum += ($value - $mean) ** 2;
            });
            $variance = $distance_sum / $this->count();

            return sqrt($variance);
        });
    }
}
