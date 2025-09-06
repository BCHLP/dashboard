<?php
declare(strict_types=1);

namespace App\Services;

use App\Actions\ValvePosition;
use App\Enums\NodeTypeEnum;
use App\Enums\TreatmentStageEnum;
use App\Models\Node;
use App\Models\TreatmentLine;

class AutomationService
{
    public function run()
    {

        $lines = TreatmentLine::with('tanks')->get();
        $valvePosition = app(ValvePosition::class);
        foreach ($lines as $line) {
            $tank1 = $line->tanks->where('name', "SED-{$line->name}1")->first();
            $tank2 = $line->tanks->where('name', "AER-{$line->name}2")->first();
            $tank3 = $line->tanks->where('name', "SED-{$line->name}3")->first();

            /**
             * Handling stage 1
             */
            $stage1 = $this->handleStage(1, $line, $line->stage_1, $tank1);
            if ($stage1 !== $line->stage_1) {
                $line->stage_1 = $stage1;
                $line->save();
            }

            if ($stage1 === TreatmentStageEnum::TRANSFERRING && $line->stage_2 !== TreatmentStageEnum::FILLING) {
                $line->stage_2 = TreatmentStageEnum::FILLING;
                $line->save();
                $valvePosition($tank2->children->first(), 0);
            }

            /**
             * Handling stage 2
             */
            $stage2 = $this->handleStage(2, $line, $line->stage_2, $tank2);
            if ($stage2 !== $line->stage_2) {
                $line->stage_2 = $stage2;
                $line->save();
            }

            if ($stage2 === TreatmentStageEnum::TRANSFERRING && $line->stage_3 !== TreatmentStageEnum::FILLING) {
                $line->stage_3 = TreatmentStageEnum::FILLING;
                $line->save();
                $valvePosition($tank3->children->first(), 0);
            }

            /**
             * Handling stage 3
             */
            $stage3 = $this->handleStage(3, $line, $line->stage_3, $tank3);
            if ($stage3 !== $line->stage_3) {
                $line->stage_3 = $stage3;
                $line->save();
            }
        }

    }

    private function handleStage(int $stageNumber, TreatmentLine $line, TreatmentStageEnum $stage, Node $tank) : TreatmentStageEnum {

        $tankService = new TankService($tank);
        $valvePosition = app(ValvePosition::class);

        switch ($stage) {
            case TreatmentStageEnum::AVAILABLE:
                if ($stageNumber !== 1) {
                    break;
                }
                if (!TreatmentLine::where('stage_1',TreatmentStageEnum::FILLING)->exists()) {

                    $valvePosition($tank->parent, 100);
                    $valvePosition($tank->children->first(), 0);

                    if ($tank->parent->parent->node_type === NodeTypeEnum::SCREEN) {
                        $masterValve = $tank->parent->parent->parent;
                        $valvePosition($masterValve, 100);
                    }

                    return TreatmentStageEnum::FILLING;
                }
                break;
            case TreatmentStageEnum::FILLING:
                if ($tankService->isFilled()) {

                    $valvePosition($tank->parent, 0);
                    $tank->settings()->where('name', 'filled_time')->update(['value' => time()]);

                    return TreatmentStageEnum::PROCESSING;
                }
                break;
            case TreatmentStageEnum::PROCESSING:
                if ($tank->parent->parent->node_type === NodeTypeEnum::SCREEN) {
                    $masterValve = $tank->parent->parent->parent;
                    $masterValveService = new ValveService($masterValve);
                    if ($masterValveService->isOpened()) {
                        $valvePosition($masterValve, 0);
                    }
                }

                $filledTime = $tank->settings()->where('name', 'filled_time')->first()->value() ?? 0;

                $destinationIsAvailable = true;
                $destination = $tank->children->first()->children->first();
                if ($destination->isTank()) {
                    $destinationTankService = new TankService($destination);
                    $destinationIsAvailable = $destinationTankService->isEmpty();
                }

                if ($destinationIsAvailable && time() - $filledTime > 10) {

                    $valvePosition($tank->children->first(), 100);
                    $valvePosition($tank->parent, 0);

                    $tank->settings()->where('name', 'filled_time')->update(['value' => 0]);
                    return TreatmentStageEnum::TRANSFERRING;
                }
                break;
            case TreatmentStageEnum::TRANSFERRING:
                if ($tankService->isEmpty()) {

                    $valvePosition($tank->children->first(), 0);
                    return TreatmentStageEnum::AVAILABLE;
                }
        }

        return $stage;
    }

}
