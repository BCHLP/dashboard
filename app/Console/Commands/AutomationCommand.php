<?php

namespace App\Console\Commands;

use App\Actions\ValvePosition;
use App\Enums\TreatmentStageEnum;
use App\Models\Node;
use App\Models\TreatmentLine;
use App\Services\AutomationService;
use App\Services\MetricService;
use App\Services\TankService;
use Illuminate\Console\Command;

class AutomationCommand extends Command
{
    protected $signature = 'automation {--status} {--once}';

    protected $description = 'Command description';

    public function handle(): void
    {
        if ($this->option('status')) {
            $this->table(['LINE','TANK 1', 'LEVEL', 'TANK 2',  'LEVEL', 'TANK 3', 'LEVEL'], $this->lineTable());
            return;
        }

        $automation = new AutomationService();
        while(true) {


            $automation->run();
            $this->output->write("\033[2J\033[H");
            $this->table(['LINE','TANK 1', 'LEVEL', 'TANK 2',  'LEVEL', 'TANK 3', 'LEVEL'], $this->lineTable());

            if ($this->option('once')) {
                return;
            }

            sleep(2);
        }
    }

    private function lineTable() : array {
        $table = [];
        $lines = TreatmentLine::query()->orderBy('name')->get();
        foreach ($lines as $line) {

            $tank1 = new TankService("SED-{$line->name}1");
            $tank2 = new TankService("AER-{$line->name}2");
            $tank3 = new TankService("SED-{$line->name}3");

            $extras = [1 => '', 2 => '', 3 => ''];
            if ($line->stage_1 === TreatmentStageEnum::PROCESSING) {
                $extras[1] = " " . $this->getSecondsLeftForProcessing($tank1) . '(s)';
            }
            if ($line->stage_2 === TreatmentStageEnum::PROCESSING) {
                $extras[2] = " " . $this->getSecondsLeftForProcessing($tank2) . '(s)';
            }
            if ($line->stage_3 === TreatmentStageEnum::PROCESSING) {
                $extras[3] = " " . $this->getSecondsLeftForProcessing($tank3) . '(s)';
            }

            $table[] = [
                $line->name,
                $line->stage_1->name . $extras[1],
                    "({$tank1->getLevel()}%)",
                $line->stage_2->name . $extras[2],
                    " ({$tank2->getLevel()}%)",
                $line->stage_3->name . $extras[3],
                " ({$tank3->getLevel()}%)"];
        }
        return $table;

    }

    private function getSecondsLeftForProcessing(TankService $tank) : int {
        $filledTime = $tank->tank()->settings()->where('name', 'filled_time')->first()->value();
        $processedTime = $filledTime + 10;
        return $processedTime - time();
    }

}
