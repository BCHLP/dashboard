<?php

use App\Enums\MetricAliasEnum;
use App\Enums\NodeTypeEnum;
use App\Models\Datapoint;
use App\Models\MetricBaseline;
use App\Services\BaselineService;
use App\Services\MetricService;
use Carbon\Carbon;

it('can generate baseline data', function () {

    expect(MetricBaseline::count())->toBe(0)
        ->and(Datapoint::count())->toBe(0);

    $server = createServer();
    $startHour = Carbon::now()->subHours(2)->hour;
    $metrics = MetricService::getMetricKeys();

    foreach (range(0, 59) as $minute) {
        $this->travelTo(now()->setHour($startHour)->setMinute($minute)->setSecond(0));

        $p = Datapoint::factory([
            'metric_id' => $metrics[MetricAliasEnum::CPU->value],
            'source_id' => $server->id,
            'source_type' => NodeTypeEnum::SERVER,
            'value' => $minute,
        ])->create();
    }

    expect(Datapoint::count())->toBe(60)
        ->and(MetricBaseline::count())->toBe(0);

    $this->travelBack();

    $service = new BaselineService;
    $service->execute();

    expect(MetricBaseline::count())->toBe(5);

    $baseline = MetricBaseline::where('metric_id', $metrics[MetricAliasEnum::CPU->value])->first();
    expect($baseline->mean)->toBe('29.5')
        ->and($baseline->median)->toBe('29.5')
        ->and($baseline->sd)->toBe('17.318102282487');
});
