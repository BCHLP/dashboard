<?php

use App\Models\Datapoint;
use App\Models\DeviceMetric;
use App\Models\MetricBaseline;
use App\Models\Server;
use App\Models\Metric;
use App\Services\BaselineService;
use Carbon\Carbon;

it('can generate baseline data', function () {

    \Illuminate\Support\Facades\Event::fake();

    $metric = Metric::factory()->create();
    $server = Server::factory()->create();
    $device = DeviceMetric::factory()->create([
        'metric_id' => $metric->id,
        'device_metric_id' => $server->id,
        'device_metric_type' => Server::class,
    ]);

    $startHour = Carbon::now()->subHours(2)->hour;



    foreach(range(0,59) as $minute) {
        $this->travelTo(now()->setHour($startHour)->setMinute($minute)->setSecond(0));
        Datapoint::factory([
            'metric_id' => $metric->id,
            'device_metric_id' => $device->id,
            'value' => $minute,
        ])->create();
    }

    expect(Datapoint::count())->toBe(60)
        ->and(MetricBaseline::count())->toBe(0);

    $this->travelBack();

    $service = app(BaselineService::class);
    $service();

    expect(MetricBaseline::count())->toBe(1);

    $baseline = MetricBaseline::first();
    expect($baseline->mean)->toBe("29.5")
        ->and($baseline->median)->toBe("29.5")
        ->and($baseline->sd)->toBe("17.318102282487");
});
