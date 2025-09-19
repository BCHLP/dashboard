<?php


use App\Actions\ValvePosition;
use App\Enums\TreatmentStageEnum;
use App\Events\DatapointCreatedEvent;
use App\Models\Datapoint;
use App\Models\NodeSetting;
use App\Services\AutomationService;
use App\Services\MetricService;
use App\Services\SimulatorService;
use App\Services\TankService;

test('a new line will open up when the current line changes to processing stage', function() {
    Event::fake([DatapointCreatedEvent::class]);
    $lineA = createLine(['name' => 'A', 'stage_1' => TreatmentStageEnum::FILLING]);
    $lineB = createLine(['name' => 'B', 'stage_1' => TreatmentStageEnum::AVAILABLE]);


    $metrics = MetricService::getMetricKeys();

    $valveA0 = createValve($lineA, "VAL-A0");
    $screenA1 = createScreen($lineA, "SCR-A1", $valveA0);
    $valveA1 = createValve($lineA, "VAL-A1", $screenA1);
    $tankA1 = createTank($lineA, "SED-A1", $valveA1);
    $valveA2 = createValve($lineA, "VAL-A2", $tankA1);
    $tankA2 = createTank($lineA, "AER-A2", $valveA2);
    $valveA3 = createValve($lineA, "VAL-A3", $tankA2);
    $tankA3 = createTank($lineA, "SED-A3", $valveA3);
    $valveA4 = createValve($lineA, "VAL-A4", $tankA3);

    $valveB0 = createValve($lineB, "VAL-B0");
    $screenB1 = createScreen($lineB, "SCR-B1", $valveB0);
    $valveB1 = createValve($lineB, "VAL-B1", $screenB1);
    $tankB1 = createTank($lineB,  "SED-B1", $valveB1);
    $valveB2 = createValve($lineB, "VAL-B2", $tankB1);
    $tankB2 = createTank($lineB,  "AER-B2", $valveB2);
    $valveB3 = createValve($lineB, "VAL-B3", $tankB2);
    $tankB3 = createTank($lineB,  "SED-B3", $valveB3);
    $valveB4 = createValve($lineA, "VAL-A3", $tankB3);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valveB1, 0);

    // fill the tank
    Datapoint::create([
        'time' => time(),
        'node_id'=>$tankA1->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 100]);

    $tankServiceA1 = new TankService($tankA1);
    $tankServiceB1 = new TankService($tankB1);
    expect($tankServiceA1->getLevel())->toBe(100)
        ->and($tankServiceB1->getLevel())->toBe(0)
        ->and($lineA->stage_1)->toBe(TreatmentStageEnum::FILLING)
        ->and($lineB->stage_1)->toBe(TreatmentStageEnum::AVAILABLE);


    (new AutomationService())->run();
    (new SimulatorService())->run();

    $lineA->refresh();
    $lineB->refresh();

    expect($tankServiceA1->reset()->getLevel())->toBe(90)
        ->and($lineA->stage_1)->toBe(TreatmentStageEnum::PROCESSING)
        ->and($lineB->stage_1)->toBe(TreatmentStageEnum::FILLING)
        ->and(NodeSetting::where('node_id', $valveA1->id)->where('name', 'opened')->first()->value())->toBe(0)
        ->and(NodeSetting::where('node_id', $valveB1->id)->where('name', 'opened')->first()->value())->toBe(100);
});

it("will set the next tank to FILLING if it is ready", function() {
    Event::fake([DatapointCreatedEvent::class]);
    $lineA = createLine(['name' => 'A', 'stage_1' => TreatmentStageEnum::PROCESSING, 'stage_2' => TreatmentStageEnum::AVAILABLE]);

    $metrics = MetricService::getMetricKeys();

    $valveA0 = createValve($lineA, "VAL-A0");
    $screenA1 = createScreen($lineA, "SCR-A1", $valveA0);
    $valveA1 = createValve($lineA, "VAL-A1", $screenA1);
    $tankA1 = createTank($lineA, "SED-A1", $valveA1);
    $valveA2 = createValve($lineA, "VAL-A2", $tankA1);
    $tankA2 = createTank($lineA, "AER-A2", $valveA2);
    $valveA3 = createValve($lineA, "VAL-A3", $tankA2);
    $tankA3 = createTank($lineA, "SED-A3", $valveA3);
    $valveA4 = createValve($lineA, "VAL-A4", $tankA3);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valveA1, 0);
    $valvePosition($valveA2, 0);
    $valvePosition($valveA3, 0);
    $valvePosition($valveA4, 0);

    // fill the tank
    Datapoint::create([
        'time' => time(),
        'node_id'=>$tankA1->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 100]);

    $tankServiceA1 = new TankService($tankA1);
    $tankServiceB1 = new TankService($tankA2);
    expect($tankServiceA1->getLevel())->toBe(100)
        ->and($tankServiceB1->getLevel())->toBe(0)
        ->and($lineA->stage_1)->toBe(TreatmentStageEnum::PROCESSING)
        ->and($lineA->stage_2)->toBe(TreatmentStageEnum::AVAILABLE);

    $tankA1->settings()->where('name', 'filled_time')->update(['value' => time() - 3600]);

    (new AutomationService())->run();
    (new SimulatorService())->run();

    $lineA->refresh();

    expect($tankServiceA1->reset()->getLevel())->toBe(90)
        ->and($lineA->stage_1)->toBe(TreatmentStageEnum::TRANSFERRING)
        ->and($lineA->stage_2)->toBe(TreatmentStageEnum::FILLING)
        ->and(NodeSetting::where('node_id', $valveA1->id)->where('name', 'opened')->first()->value())->toBe(0)
        ->and(NodeSetting::where('node_id', $valveA2->id)->where('name', 'opened')->first()->value())->toBe(100);
});

it("will not set the next tank to FILLING if it is not ready", function() {
    Event::fake([DatapointCreatedEvent::class]);
    $lineA = createLine(['name' => 'A', 'stage_1' => TreatmentStageEnum::PROCESSING, 'stage_2' => TreatmentStageEnum::TRANSFERRING]);

    $metrics = MetricService::getMetricKeys();

    $valveA0 = createValve($lineA, "VAL-A0");
    $screenA1 = createScreen($lineA, "SCR-A1", $valveA0);
    $valveA1 = createValve($lineA, "VAL-A1", $screenA1);
    $tankA1 = createTank($lineA, "SED-A1", $valveA1);
    $valveA2 = createValve($lineA, "VAL-A2", $tankA1);
    $tankA2 = createTank($lineA, "AER-A2", $valveA2);
    $valveA3 = createValve($lineA, "VAL-A3", $tankA2);
    $tankA3 = createTank($lineA, "SED-A3", $valveA3);
    $valveA4 = createValve($lineA, "VAL-A4", $tankA3);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valveA1, 0);
    $valvePosition($valveA2, 0);
    $valvePosition($valveA3, 0);
    $valvePosition($valveA4, 0);

    // fill the tank
    Datapoint::create([
        'time' => time(),
        'node_id'=>$tankA1->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 100]);

    Datapoint::create([
        'time' => time(),
        'node_id'=>$tankA2->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 50]);

    $tankServiceA1 = new TankService($tankA1);
    $tankServiceB1 = new TankService($tankA2);
    expect($tankServiceA1->getLevel())->toBe(100)
        ->and($tankServiceB1->getLevel())->toBe(50)
        ->and($lineA->stage_1)->toBe(TreatmentStageEnum::PROCESSING)
        ->and($lineA->stage_2)->toBe(TreatmentStageEnum::TRANSFERRING);

    $tankA1->settings()->where('name', 'filled_time')->update(['value' => time() - 3600]);

    (new AutomationService())->run();
    (new SimulatorService())->run();

    $lineA->refresh();

    expect($tankServiceA1->reset()->getLevel())->toBe(100)
        ->and($lineA->stage_1)->toBe(TreatmentStageEnum::PROCESSING)
        ->and($lineA->stage_2)->toBe(TreatmentStageEnum::TRANSFERRING)
        ->and(NodeSetting::where('node_id', $valveA1->id)->where('name', 'opened')->first()->value())->toBe(0)
        ->and(NodeSetting::where('node_id', $valveA2->id)->where('name', 'opened')->first()->value())->toBe(0);
});
