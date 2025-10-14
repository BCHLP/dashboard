<?php


use App\Enums\MetricAliasEnum;
use App\Enums\NodeTypeEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;
use App\Services\MetricService;

test('non authenticated servers cannot connect', function () {

});

test('a server can store one or more datapoints', function () {

    $createServer = app(\App\Actions\CreateServer::class);
    $create = $createServer("server");
    $token = $create['token'];
    $server = $create['server'];

    $metricKeys = MetricService::getMetricKeys();

    $this->actingAs($server);
    expect(Datapoint::count())->toBe(0);

    $time = time();

    $response = $this->post(route('api.datapoints.store'), [
        'points' => [
            [
                'time' => $time,
                'metric' => MetricAliasEnum::CPU->value,
                'value' => 5,
            ],
            [
                'time' => $time,
                'metric' => MetricAliasEnum::NETWORK_BYTES_IN->value,
                'value' => 100,
            ],[
                'time' => $time,
                'metric' => MetricAliasEnum::WATER_LEVEL->value,
                'value' => 75,
            ]
        ]
    ]);

    expect(Datapoint::count())->toBe(2);
    expect(Datapoint::where('source_id', $server->id)
        ->where('source_type', Node::class)
        ->where('metric_id', $metricKeys[MetricAliasEnum::CPU->value])
        ->where('value', 5)
        ->where('time', $time)
        ->exists())->toBeTrue();

    expect(Datapoint::where('source_id', $server->id)
        ->where('source_type', Node::class)
        ->where('metric_id', $metricKeys[MetricAliasEnum::NETWORK_BYTES_IN->value])
        ->where('value', 100)
        ->where('time', $time)
        ->exists())->toBeTrue();

    expect(Datapoint::where('source_id', $server->id)
        ->where('source_type', Node::class)
        ->where('metric_id', $metricKeys[MetricAliasEnum::NETWORK_BYTES_OUT->value])
        ->where('value', 75)
        ->where('time', $time)
        ->exists())->toBeFalse();

    $response->assertNoContent();
});
