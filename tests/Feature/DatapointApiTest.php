<?php


use App\Enums\NodeTypeEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;

test('non authenticated servers cannot connect', function () {

});

test('a server can store one or more datapoints', function () {

    $server = Node::factory(['node_type' => NodeTypeEnum::SERVER])->create();
    $token = $server->createToken('test');

    $this->actingAs($server);

    $fr = Metric::factory(['alias' => 'fr'])->create();
    $wl = Metric::factory(['alias' => 'wl'])->create();
    $cpu = Metric::factory(['alias' => 'cpu'])->create();

    // cpu datapoint should not be created in this test because it will be missing from the metrics relationship
    $server->metrics()->sync([$fr->id, $wl->id]);

    expect(Datapoint::count())->toBe(0);

    $time = time();

    $response = $this->post(route('api.datapoints.store'), [
        'points' => [
            [
                'time' => $time,
                'metric' => 'fr',
                'value' => 5,
            ],
            [
                'time' => $time,
                'metric' => 'wl',
                'value' => 100,
            ],[
                'time' => $time,
                'metric' => 'cpu',
                'value' => 75,
            ]
        ]
    ]);

    expect(Datapoint::count())->toBe(2);
    expect(Datapoint::where('node_id', $server->id)
        ->where('metric_id', $fr->id)
        ->where('value', 5)
        ->where('time', $time)
        ->exists())->toBeTrue();

    expect(Datapoint::where('node_id', $server->id)
        ->where('metric_id', $wl->id)
        ->where('value', 100)
        ->where('time', $time)
        ->exists())->toBeTrue();

    expect(Datapoint::where('node_id', $server->id)
        ->where('metric_id', $cpu->id)
        ->where('value', 75)
        ->where('time', $time)
        ->exists())->toBeFalse();

    $response->assertNoContent();
});
