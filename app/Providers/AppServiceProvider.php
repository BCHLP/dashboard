<?php

namespace App\Providers;

use App\Events\UserCreatingEvent;
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
            $mean = $this->avg();
            $variance = $this->map(fn($value) => pow($value - $mean, 2))->avg();
            return sqrt($variance);
        });
    }
}
