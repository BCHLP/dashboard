<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\NodeTypeEnum;
use App\Enums\TreatmentStageEnum;
use App\Models\Datapoint;
use App\Models\Node;
use Illuminate\Support\Collection;

class SimulatorService
{
    private array $metrics = [];

    public function __construct() {
        $this->metrics = MetricService::getMetricKeys();
    }

    public function run() {
        $nodes = Node::with(['settings', 'metrics','parent', 'children','treatmentLine'])->defaultOrder()->get();
        foreach ($nodes as $node) {

            switch ($node->node_type) {
                case NodeTypeEnum::VALVE;
                case NodeTypeEnum::PUMP;
                    $this->handleValve($node);
                    break;
            }
        }
    }

    private function handleValve(Node $node) : void {

        if (blank($node->parent)) {
            return;
        }

        $flow = 0;
        $valveService = new ValveService($node);
        if ($node->parent->node_type === NodeTypeEnum::SCREEN && $valveService->isOpened()) {
            $flow = 10;
        } else if ($node->parent->isTank()) {
            $tankService = new TankService($node->parent);
            if ($tankService->getLevel() > 0 && $valveService->isOpened()) {
                $flow = 10;
                $level = $tankService->getLevel() - $flow;
                if ($level < 0) {
                    $level = 0;
                }
                $tankService->setLevel($level);
            }
        }

        if ($flow === 0) {
            return;
        }

        $tanks = $node->children->filter(function($child) {
            return $child->isTank();
        });

        if (count($tanks) > 0) {
            $split = intval(round($flow / $tanks->count()));
            foreach ($tanks as $tank) {
                $tankService = new TankService($tank);
                $level = $tankService->getLevel() + $split;
                $tankService->setLevel($level);

            }
        }

    }
}
