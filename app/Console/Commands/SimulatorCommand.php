<?php

namespace App\Console\Commands;

use App\Enums\NodeTypeEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;
use App\Services\MetricService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SimulatorCommand extends Command
{
    protected $signature = 'simulator';

    protected $description = 'Command description';

    private array $disabledPaths = [];

    private array $metrics = [];

    public function handle(): void
    {

        $this->metrics = MetricService::getMetricKeys();

        $nodes = Node::with(['settings', 'metrics'])->defaultOrder()->get();
        while(true) {
            $this->line('---------------------------------');
            $activeOutlets = [];
            foreach ($nodes as $node) {

                if (in_array($node->_lft, $this->disabledPaths) && in_array($node->_rgt, $this->disabledPaths)) {
                    $this->info("NO FLOW:" . $node->_lft . ',' . $node->_rgt . ', ' . $node->name);
                    continue;
                }

                switch ($node->node_type) {
                    case NodeTypeEnum::VALVE;
                    case NodeTypeEnum::PUMP;
                        if (!$this->handleValve($node)) {
                            $this->warn("Flow stopped at valve " . $node->name);
                            continue 2;
                        }
                        break;
                    case NodeTypeEnum::AERATION_TANK:
                    case NodeTypeEnum::SEDIMENTATION_TANK:
                        if (!$this->handleTank($node)) {
                            $this->warn("Flow stopped at tank {$node->name}");
                            continue 2;
                        } else {
                            $this->info("Tank {$node->name} at capacity");
                        }
                        break;

                    case NodeTypeEnum::OUTLET:
                        $activeOutlets[] = $node->name;
                        break;

                }

                $this->line($node->name);
            }

            if (count($activeOutlets) === 0) {
                $this->error("No flow received to any outlets");
            }

            foreach($activeOutlets as $activeOutlet) {
                $this->info("{$activeOutlet} received flow");
            }

            sleep(1);
            $this->disabledPaths = [];
        }
    }

    private function handleValve(Node $node) : bool {
        if ($node->settings->where('name','opened')->first()?->value() === 0) {
            $this->warn($node->name . " valve is closed");
            $this->disablePath($node->_lft, $node->_rgt);
            return false;
        }
        return true;
    }

    private function handleTank(Node $node) : bool {
        $capacity = $node->settings()->where('name','capacity')->first();

        $level = Datapoint::where('node_id',$node->id)
            ->where('metric_id',$this->metrics['wl'])
            ->latest()
            ->limit(1)
            ->first()
            ->value ?? 0;

        if ($level < $capacity->value()) {
            $level +=10;
            $this->disablePath($node->_lft, $node->_rgt);
            if ($level > $capacity->value()) {
                $level = $capacity->value();
            }
        }

        $point = Datapoint::create([
            'node_id'=>$node->id,
            'metric_id'=>$this->metrics['wl'],
            'value'=>$level]);

        $this->info("{$node->name} level at {$level}%");


        return $capacity->value() === $level;
    }

    private function disablePath(int $lft, int $rgt) : void {
        $this->disabledPaths = array_merge($this->disabledPaths, range($lft, $rgt));
    }
}
