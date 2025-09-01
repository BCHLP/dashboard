<?php

namespace App\Services;

use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\MetricBaseline;
use Carbon\Carbon;

class BaselineService
{
    public function __invoke() : void
    {
        $metrics = Metric::all();
        $start = Carbon::now()->subHours(2)->setMinutes(0)->setSeconds(0);
        $end = Carbon::now()->subHours()->setMinutes(0)->setSeconds(0);
        $dow = $start->dayOfWeek();
        foreach ($metrics as $metric) {
            foreach($metric->devices as $device) {

                $datapoints = Datapoint::where('metric_id', $metric->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->pluck('value');

                if ($datapoints->count() === 0) {
                    continue;
                }

                MetricBaseline::updateOrCreate([
                    'metric_id' => $metric->id,
                    'device_metric_id' => $device->id,
                    'dow' => $dow,
                    'hour' => $start->hour,
                ],[
                    'mean' => $datapoints->avg(),
                    'median' => $datapoints->median(),
                    'sd' => $datapoints->sd(),
                ]);
            }
        }
    }
}
