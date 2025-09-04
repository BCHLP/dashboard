<?php

namespace App\Console\Commands;

use App\Actions\ValvePosition;
use App\Enums\TreatmentStageEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;
use App\Models\TreatmentLine;
use App\Services\MetricService;
use App\Services\TankService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AutomationCommand extends Command
{
    protected $signature = 'automation';

    protected $description = 'Command description';

    private array $metrics = [];

    public function handle(): void
    {
        $lines = TreatmentLine::all();
        $this->metrics = MetricService::getMetricKeys();

        foreach ($lines as $line) {
            switch($line->stage) {
                case TreatmentStageEnum::TANK1_FILLING:
                    $this->info("Tank 1 is filling");
                    $this->tank1Filling($line);
                    break;
                case TreatmentStageEnum::TANK1_PROCESSING:
                    $this->info("Tank 1 is processing");
                    $this->tank1Processing($line);
                    break;
                case TreatmentStageEnum::TANK1_TRANSFER_TANK2:
                    $this->info("Tank 1 is transferring into Tank 2");
                    $this->tank1Transferring($line);
                    break;
                case TreatmentStageEnum::TANK2_PROCESSING:
                    $this->info("Tank 2 is processing");
                    $this->tank2Processing($line);
                    break;
                case TreatmentStageEnum::TANK2_TRANSFER_TANK3:
                    $this->info("Tank 2 is transferring into Tank 3");
                    $this->tank2Transferring($line);
                    break;
                case TreatmentStageEnum::TANK3_PROCESSING:
                    $this->info("Tank 3 is processing");
                    $this->tank3Processing($line);
                    break;
                case TreatmentStageEnum::TANK3_EMPTYING:
                    $this->info("Tank 3 is emptying");
                    $this->tank3Emptying($line);
                    break;
                case TreatmentStageEnum::AVAILABLE:
                    $this->info("Line is available");
            }
        }
    }

    private function tank1Filling(TreatmentLine $line) : void {
        $node = Node::with('settings')->where('name',"SED-{$line->name}1")->first();
        if (!$node) {
            $this->error("Did not find  node SED-{$line->name}1");
            return;
        }

        $percent = ( new TankService($node))->getLevelPercentage();
        if ($percent > 95) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}1",0);

            $line->stage = TreatmentStageEnum::TANK1_PROCESSING;
            $line->save();

            $node->settings()->where('name','filled_time')->update(['value' => time()]);
        }

    }

    private function tank1Processing(TreatmentLine $line) : void {
        $node = Node::with('settings')->where('name',"SED-{$line->name}1")->first();
        $filledTime = $node->settings()->where('name','filled_time')->first()->value() ?? 0;
        if (time() - $filledTime > 60) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}2",100);
            $moveValve("VAL-{$line->name}3",0);

            $line->stage = TreatmentStageEnum::TANK1_TRANSFER_TANK2;
            $line->save();

            $node->settings()->where('name','filled_time')->update(['value' => 0]);
        }
    }

    private function tank1Transferring(TreatmentLine $line) : void {
        $node = Node::with('settings')->where('name',"SED-{$line->name}1")->first();
        $level = ( new TankService($node))->getLevel();
        if ($level === 0) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}2",0);

            $line->stage = TreatmentStageEnum::TANK2_PROCESSING;
            $line->save();

            $node->settings()->where('name','filled_time')->update(['value' => time()]);
        }
    }

    private function tank2Processing(TreatmentLine $line) : void {
        $node = Node::with('settings')->where('name',"SED-{$line->name}2")->first();
        $filledTime = $node->settings()->where('name','filled_time')->first()->value() ?? 0;
        if (time() - $filledTime > 60) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}2",100);
            $moveValve("VAL-{$line->name}3",0);

            $line->stage = TreatmentStageEnum::TANK2_TRANSFER_TANK3;
            $line->save();

            $node->settings()->where('name','filled_time')->update(['value' => 0]);
        }
    }

    private function tank2Transferring(TreatmentLine $line) : void {
        $node = Node::with('settings')->where('name',"SED-{$line->name}2")->first();
        $level = ( new TankService($node))->getLevel();
        if ($level === 0) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}3",0);

            $line->stage = TreatmentStageEnum::TANK3_PROCESSING;
            $line->save();

            $node->settings()->where('name','filled_time')->update(['value' => time()]);
        }
    }

    private function tank3Processing(TreatmentLine $line) : void {
        $node = Node::with('settings')->where('name',"SED-{$line->name}3")->first();
        $filledTime = $node->settings()->where('name','filled_time')->first()->value() ?? 0;
        if (time() - $filledTime > 60) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}4",100);

            $line->stage = TreatmentStageEnum::TANK3_EMPTYING;
            $line->save();

            $node->settings()->where('name','filled_time')->update(['value' => 0]);
        }
    }

    private function tank3Emptying(TreatmentLine $line) : void {
        $node = Node::with('settings')->where('name',"SED-{$line->name}3")->first();
        $level = ( new TankService($node))->getLevel();
        if ($level === 0) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}4",0);

            $line->stage = TreatmentStageEnum::AVAILABLE;
            $line->save();

            $node->settings()->where('name','filled_time')->update(['value' => time()]);
        }
    }
}
