<?php

namespace App\Console\Commands;

use App\Enums\NodeTypeEnum;
use App\Models\Node;
use App\Services\SimulatorService;
use App\Services\TankService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SimulatorCommand extends Command
{
    protected $signature = 'simulator {treatment_line_id} {--status} {--once}';

    protected $description = 'Command description';

    public function handle():void {

        if ($this->option('status')) {
            $nodes = Node::with(['settings', 'metrics'])->defaultOrder()->get();
            $this->table(['TANK','WATER-LEVEL'], $this->tankTable($nodes));
            $this->table(['VALVE','STATUS'], $this->valveTable($nodes));
            return;
        }

        $service = new SimulatorService($this->argument('treatment_line_id'));

        while(true) {

            $service->run();

            $nodes = Node::with(['settings', 'metrics'])
                ->where('treatment_line_id', $this->argument('treatment_line_id'))
                ->defaultOrder()->get();
            $this->output->write("\033[2J\033[H");
            $this->table(['TANK','WATER-LEVEL'], $this->tankTable($nodes));
            $this->table(['VALVE','STATUS'], $this->valveTable($nodes));

            if ($this->option('once')) {
                return;
            }

            sleep(2);
        }
    }

    private function tankTable(Collection $nodes) : array {
        $table = [];
        $nodes->filter(function ($node) {
            return in_array($node->node_type, [NodeTypeEnum::DIGESTION_TANK, NodeTypeEnum::SEDIMENTATION_TANK, NodeTypeEnum::AERATION_TANK]);
        })->each(function ($node) use (&$table) {
            $waterLevel = (new TankService($node))->getLevelPercentage();
            $table[] = [$node->name, $waterLevel];
        });

        return $table;
    }

    private function valveTable(Collection $nodes) : array {
        $table = [];
        $nodes->filter(function ($node) {
            return in_array($node->node_type, [NodeTypeEnum::VALVE]);
        })->each(function ($node) use (&$table) {
            $status = ($node->settings()->where('name','opened')->first()?->value ?? 0) > 0;
            $table[] = [$node->name, ($status ? "OPEN" : "CLOSED")];
        });

        return $table;
    }
}
