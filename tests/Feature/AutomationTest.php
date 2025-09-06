<?php


use App\Actions\ValvePosition;
use App\Enums\TreatmentStageEnum;
use App\Events\DatapointCreatedEvent;
use App\Models\Datapoint;
use App\Models\NodeSetting;
use App\Models\TreatmentLine;
use App\Services\AutomationService;
use App\Services\MetricService;
use App\Services\SimulatorService;
use App\Services\TankService;

test('a new line will open up when the current line changes to processing stage', function() {
    Event::fake([DatapointCreatedEvent::class]);
    $lineA = createLine(['name' => 'A', 'stage' => TreatmentStageEnum::TANK1_FILLING]);
    $lineB = createLine(['name' => 'B', 'stage' => TreatmentStageEnum::AVAILABLE]);


    $metrics = MetricService::getMetricKeys();

    $valveA1 = createValve($lineA, "VAL-A1");
    $tankA1 = createTank($lineA, "SED-A1", $valveA1);
    $valveA2 = createValve($lineA, "VAL-A2", $tankA1);
    $tankA2 = createTank($lineA, "SED-A2", $valveA2);
    $valveA3 = createValve($lineA, "VAL-A3", $tankA2);

    $valveB1 = createValve($lineB, "VAL-B1");
    $tankB1 = createTank($lineB,  "SED-B1", $valveB1);
    $valveB2 = createValve($lineB, "VAL-B2", $tankB1);
    $tankB2 = createTank($lineB,  "SED-B2", $valveB2);
    $valveB3 = createValve($lineB, "VAL-B3", $tankB2);

    $valvePosition = app(ValvePosition::class);
    $valvePosition($valveB1, 0);

    // fill the tank
    Datapoint::create([
        'node_id'=>$tankA1->id,
        'metric_id'=> $metrics['wl'],
        'value'=> 100]);

    $tankServiceA1 = new TankService($tankA1);
    $tankServiceB1 = new TankService($tankB1);
    expect($tankServiceA1->getLevel())->toBe(100)
        ->and($tankServiceB1->getLevel())->toBe(0)
        ->and($lineA->stage)->toBe(TreatmentStageEnum::TANK1_FILLING)
        ->and($lineB->stage)->toBe(TreatmentStageEnum::AVAILABLE);


    (new AutomationService())->run();
    (new SimulatorService())->run();

    $lineA->refresh();
    $lineB->refresh();

    expect($tankServiceA1->reset()->getLevel())->toBe(90)
        ->and($lineA->stage)->toBe(TreatmentStageEnum::TANK1_PROCESSING)
        ->and($lineB->stage)->toBe(TreatmentStageEnum::TANK1_FILLING)
        ->and(NodeSetting::where('node_id', $valveA1->id)->where('name', 'opened')->first()->value())->toBe(0)
        ->and(NodeSetting::where('node_id', $valveB1->id)->where('name', 'opened')->first()->value())->toBe(100);
});
