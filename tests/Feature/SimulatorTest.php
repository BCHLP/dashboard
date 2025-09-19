<?php


use App\Actions\ValvePosition;
use App\Enums\NodeTypeEnum;
use App\Enums\TreatmentStageEnum;
use App\Events\DatapointCreatedEvent;
use App\Models\Datapoint;
use App\Models\Node;
use App\Services\MetricService;
use App\Services\SimulatorService;
use App\Services\TankService;

test('tank 1 will fill up with water if valve 1 is open', function () {

    Event::fake([DatapointCreatedEvent::class]);
    $line = createLine();

    $screen = createScreen($line, "screen1");
    $valve1 = createValve($line, 'valve1', $screen);
    $tank1 = createTank($line, 'tank1', $valve1);
    $valve2 = createValve($line, 'valve2', $tank1);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valve2, 0);

    $tankService = new TankService($tank1);
    expect($tankService->getLevel())->toBe(0);

    (new SimulatorService())->run();

    $tank1->refresh();
    $tankService = new TankService($tank1);
    expect($tankService->getLevel())->toBeGreaterThan(0);

});


test('tank 1 will not fill up with water if valve 1 is closed', function () {

    Event::fake([DatapointCreatedEvent::class]);
    $line = createLine();


    $valve1 = createValve($line,'valve1');
    $tank1 = createTank($line, 'tank1', $valve1);
    $valve2 = createValve($line, 'valve2', $tank1);
    $valvePosition = app(ValvePosition::class);
    $valvePosition($valve1, 0);

    $tankService = new TankService($tank1);
    expect($tankService->getLevel())->toBe(0);

    (new SimulatorService())->run();

    $tank1->refresh();
    $tankService = new TankService($tank1);
    expect($tankService->getLevel())->toBe(0);

});

test('tank 1s flow rate will be 0 if valve 1 is open, valve 2 is closed and tank 1 is full', function () {

})->skip();

test('tank 1s flow rate will not be 0 if valve 1 is open, valve 2 is closed and tank 1 is filling', function () { })->skip();


test('tank 2 will receive water from tank 1 if tank 1 is filled and valve 2 is open', function() {

    Event::fake([DatapointCreatedEvent::class]);
$line = createLine();

    $metrics = MetricService::getMetricKeys();

    $valve1 = createValve($line, 'valve1');
    $tank1 = createTank($line, 'tank1', $valve1);
    $valve2 = createValve($line, 'valve2', $tank1);
    $tank2 = createTank($line, 'tank2', $valve2);
    $valve3 = createValve($line, 'valve3', $tank2);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valve3, 0);


    // fill the tank
    Datapoint::create([
        'time' => time(),
        'node_id'=>$tank1->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 100]);

    $tank1->refresh();
    $tank2->refresh();

    $tankService1 = new TankService($tank1);
    $tankService2 = new TankService($tank2);
    expect($tankService1->getLevel())->toBe(100)
        ->and($tankService2->getLevel())->toBe(0);

    (new SimulatorService())->run();

    expect($tankService2->reset()->getLevel())->toBeGreaterThan(0);

});

test('tank 2 will not receive water from tank 1 if tank 1 is filled and valve 2 is closed', function() {

    Event::fake([DatapointCreatedEvent::class]);
$line = createLine();

    $metrics = MetricService::getMetricKeys();

    $valve1 = createValve($line, 'valve1');
    $tank1 = createTank($line, 'tank1', $valve1);
    $valve2 = createValve($line, 'valve2', $tank1);
    $tank2 = createTank($line, 'tank2', $valve2);
    $valve3 = createValve($line, 'valve3', $tank2);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valve2, 0);
    $valvePosition($valve3, 0);

    // fill the tank
    Datapoint::create([
        'time' => time(),
        'node_id'=>$tank1->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 100]);

    $tankService1 = new TankService($tank1);
    $tankService2 = new TankService($tank2);
    expect($tankService1->getLevel())->toBe(100)
        ->and($tankService2->getLevel())->toBe(0);

    (new SimulatorService())->run();

    expect($tankService2->getLevel())->toBe(0);

});

test('tank 2 will not receive water from tank 1 if tank 1 is empty and valve is open', function() {

    Event::fake([DatapointCreatedEvent::class]);
$line = createLine();

    $metrics = MetricService::getMetricKeys();

    $valve1 = createValve($line, 'valve1');
    $tank1 = createTank($line, 'tank1', $valve1);
    $valve2 = createValve($line, 'valve2',  $tank1);
    $tank2 = createTank($line, 'tank2', $valve2);
    $valve3 = createValve($line, 'valve3', $tank2);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valve3, 0);

    $tankService1 = new TankService($tank1);
    $tankService2 = new TankService($tank2);
    expect($tankService1->getLevel())->toBe(0)
        ->and($tankService2->getLevel())->toBe(0);

    (new SimulatorService())->run();

    expect($tankService2->getLevel())->toBe(0);

});

test('tank 2 will not receive water from tank 1 if tank 1 is empty and valve is closed', function() {

    Event::fake([DatapointCreatedEvent::class]);
$line = createLine();

    $metrics = MetricService::getMetricKeys();

    $valve1 = createValve($line, 'valve1');
    $tank1 = createTank($line, 'tank1', $valve1);
    $valve2 = createValve($line, 'valve2', $tank1);
    $tank2 = createTank($line, 'tank2', $valve2);
    $valve3 = createValve($line, 'valve3', $tank2);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valve2, 0);
    $valvePosition($valve3, 0);

    $tankService1 = new TankService($tank1);
    $tankService2 = new TankService($tank2);
    expect($tankService1->getLevel())->toBe(0)
        ->and($tankService2->getLevel())->toBe(0);

    (new SimulatorService())->run();

    expect($tankService2->getLevel())->toBe(0);

});

test('tank 2 WL will increase and tank 1 WL will reduce if tank 1 has water, valve 1 is closed, value 2 is open', function() {

    Event::fake([DatapointCreatedEvent::class]);
    $line = createLine();

    $metrics = MetricService::getMetricKeys();

    $valve1 = createValve($line, 'valve1');
    $tank1 = createTank($line, 'tank1', $valve1);
    $valve2 = createValve($line, 'valve2', $tank1);
    $tank2 = createTank($line, 'tank2', $valve2);
    $valve3 = createValve($line, 'valve3', $tank2);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valve1, 0);
    $valvePosition($valve3, 0);

    // fill the tank
    Datapoint::create([
        'time' => time(),
        'node_id'=>$tank1->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 50]);

    $tankService1 = new TankService($tank1);
    $tankService2 = new TankService($tank2);
    expect($tankService1->getLevel())->toBe(50)
        ->and($tankService2->getLevel())->toBe(0);

    (new SimulatorService())->run();

    expect($tankService1->reset()->getLevel())->toBe(40)
        ->and($tankService2->reset()->getLevel())->toBe(10);

});


test("line B tank 1 will receive water if line A valve 0 is closed and line B valve 0 is open", function() {
    Event::fake([DatapointCreatedEvent::class]);
    $lineA = createLine(['name' => 'A']);
    $lineB = createLine(['name' => 'B']);

    $metrics = MetricService::getMetricKeys();

    $screenA1 = createScreen($lineA, 'SCR-A1');
    $valveA1 = createValve($lineA, "VAL-A1", $screenA1);
    $tankA1 = createTank($lineA, "SED-A1", $valveA1);
    $valveA2 = createValve($lineA, "VAL-A2", $tankA1);

    $screenB1 = createScreen($lineA, 'SCR-B1');
    $valveB1 = createValve($lineB, "VAL-B1", $screenB1);
    $tankB1 = createTank($lineB,  "SED-B1", $valveB1);
    $valveB2 = createValve($lineB, "VAL-B2", $tankB1);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valveA1, 0);
    $valvePosition($valveB2, 0);

    $tankServiceB1 = new TankService($tankB1);
        expect($tankServiceB1->getLevel())->toBe(0);

    (new SimulatorService())->run();

    $tankServiceB1->reset();
    expect($tankServiceB1->getLevel())->toBeGreaterThan(0);
});
