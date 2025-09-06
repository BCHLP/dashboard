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
    protected $signature = 'automation';

    protected $description = 'Command description';

    public function handle(): void
    {

        $automation = new AutomationService();
        while(true) {

            $automation->run();
            $this->output->write("\033[2J\033[H");
            $this->table(['LINE','STAGE'], $this->lineTable());
            sleep(1);
        }
    }

    private function lineTable() : array {
        $table = [];
        $lines = TreatmentLine::query()->orderBy('name')->get();
        foreach ($lines as $line) {
            $table[] = [$line->name, $line->stage->name];
        }
        return $table;

    }

}
