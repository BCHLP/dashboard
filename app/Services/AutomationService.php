<?php
declare(strict_types=1);

namespace App\Services;

use App\Actions\ValvePosition;
use App\Enums\TreatmentStageEnum;
use App\Models\Node;
use App\Models\TreatmentLine;
use Illuminate\Support\Collection;

class AutomationService
{

    public function run()
    {

        $lines = TreatmentLine::all();
        foreach ($lines as $line) {
            switch ($line->stage) {
                case TreatmentStageEnum::TANK1_FILLING:
                    $this->tank1Filling($line);
                    break;
                case TreatmentStageEnum::TANK1_PROCESSING:
                    $this->tank1Processing($line);
                    break;
                case TreatmentStageEnum::TANK1_TRANSFER_TANK2:
                    $this->tank1Transferring($line);
                    break;
                case TreatmentStageEnum::TANK2_PROCESSING:
                    $this->tank2Processing($line);
                    break;
                case TreatmentStageEnum::TANK2_TRANSFER_TANK3:
                    $this->tank2Transferring($line);
                    break;
                case TreatmentStageEnum::TANK3_PROCESSING:
                    $this->tank3Processing($line);
                    break;
                case TreatmentStageEnum::TANK3_EMPTYING:
                    $this->tank3Emptying($line);
                    break;
                // case TreatmentStageEnum::AVAILABLE:
            }
        }
        $this->lineCheck();
    }




    /**
     * Check if we need to open a new line, and do so if required
     * @return void
     *
     */
    private function lineCheck()
    {
        if (TreatmentLine::where('stage', TreatmentStageEnum::TANK1_FILLING)->count() > 0) {
            return;
        }

        $line = TreatmentLine::where('stage', TreatmentStageEnum::AVAILABLE)->first();
        if (!$line) {
            return;
        }

        $moveValve = app(ValvePosition::class);
        $moveValve("VAL-{$line->name}0", 100);
        $moveValve("VAL-{$line->name}1", 100);

        $line->stage = TreatmentStageEnum::TANK1_FILLING;
        $line->save();
    }

    private function tank1Filling(TreatmentLine $line): void
    {
        $node = Node::with('settings')->where('name', "SED-{$line->name}1")->first();
        if (!$node) {
            return;
        }

        $percent = (new TankService($node))->getLevelPercentage();
        if ($percent > 95) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}0", 0);
            $moveValve("VAL-{$line->name}1", 0);

            $line->stage = TreatmentStageEnum::TANK1_PROCESSING;
            $line->save();

            $node->settings()->where('name', 'filled_time')->update(['value' => time()]);

        }
    }

    private function tank1Processing(TreatmentLine $line): void
    {


        $node = Node::with('settings')->where('name', "SED-{$line->name}1")->first();
        $filledTime = $node->settings()->where('name', 'filled_time')->first()->value() ?? 0;
        if (time() - $filledTime > 15) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}2", 100);
            $moveValve("VAL-{$line->name}3", 0);

            $line->stage = TreatmentStageEnum::TANK1_TRANSFER_TANK2;
            $line->save();

            $node->settings()->where('name', 'filled_time')->update(['value' => 0]);
        }


    }

    private function tank1Transferring(TreatmentLine $line): void
    {
        $node = Node::with('settings')->where('name', "SED-{$line->name}1")->first();
        if (!$node) {
            $this->error("Can't find SED-{$line->name}1");
        }
        $level = (new TankService($node))->getLevel();
        if ($level === 0) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}2", 0);

            $line->stage = TreatmentStageEnum::TANK2_PROCESSING;
            $line->save();

            $node->settings()->where('name', 'filled_time')->update(['value' => time()]);
        }
    }

    private function tank2Processing(TreatmentLine $line): void
    {
        $node = Node::with('settings')->where('name', "SED-{$line->name}2")->first();
        if (!$node) {
            ray("Cannot find node SED-{$line->name}2");
            return;
        }
        $filledTime = $node->settings()->where('name', 'filled_time')->first()->value() ?? 0;
        if (time() - $filledTime > 60) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}2", 100);
            $moveValve("VAL-{$line->name}3", 0);

            $line->stage = TreatmentStageEnum::TANK2_TRANSFER_TANK3;
            $line->save();

            $node->settings()->where('name', 'filled_time')->update(['value' => 0]);
        }
    }

    private function tank2Transferring(TreatmentLine $line): void
    {
        $node = Node::with('settings')->where('name', "SED-{$line->name}2")->first();
        $level = (new TankService($node))->getLevel();
        if ($level === 0) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}3", 0);

            $line->stage = TreatmentStageEnum::TANK3_PROCESSING;
            $line->save();

            $node->settings()->where('name', 'filled_time')->update(['value' => time()]);
        }
    }

    private function tank3Processing(TreatmentLine $line): void
    {
        $node = Node::with('settings')->where('name', "SED-{$line->name}3")->first();
        $filledTime = $node->settings()->where('name', 'filled_time')->first()->value() ?? 0;
        if (time() - $filledTime > 60) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}4", 100);

            $line->stage = TreatmentStageEnum::TANK3_EMPTYING;
            $line->save();

            $node->settings()->where('name', 'filled_time')->update(['value' => 0]);
        }
    }

    private function tank3Emptying(TreatmentLine $line): void
    {
        $node = Node::with('settings')->where('name', "SED-{$line->name}3")->first();
        $level = (new TankService($node))->getLevel();
        if ($level === 0) {

            $moveValve = app(ValvePosition::class);
            $moveValve("VAL-{$line->name}4", 0);

            $line->stage = TreatmentStageEnum::AVAILABLE;
            $line->save();

            $node->settings()->where('name', 'filled_time')->update(['value' => time()]);
        }
    }

}
