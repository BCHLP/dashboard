<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
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

            if ($this->count() === 0) {
                return 0;
            }

            $mean = $this->avg();
            $distance_sum = 0;
            $this->each(function($value) use (&$distance_sum, $mean) {
                $distance_sum += ($value - $mean) ** 2;
            });
            $variance = $distance_sum / $this->count();

           return sqrt($variance);
        });
    }
}
