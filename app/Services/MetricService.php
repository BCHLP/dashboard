<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Metric;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;

class MetricService extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return \App\Services\MetricService::class;
    }

    public static function getMetricKeys() : array {
        return Cache::remember('metrics', 3600, function ()  {
            return Metric::pluck('id','alias')->toArray();
        });
    }
}
