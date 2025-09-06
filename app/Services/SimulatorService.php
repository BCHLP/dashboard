<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\NodeTypeEnum;
use App\Models\Datapoint;
use App\Models\Node;
use Illuminate\Support\Collection;

class SimulatorService
{
    private array $disabledPaths = [];

    private array $metrics = [];

    public function __construct() {
        $this->metrics = MetricService::getMetricKeys();
    }

    public function run() {
        $nodes = Node::with(['settings', 'metrics','parent', 'children'])->defaultOrder()->get();
        foreach ($nodes as $node) {

            switch ($node->node_type) {
                case NodeTypeEnum::VALVE;
                case NodeTypeEnum::PUMP;
                    $this->handleValve($node);
                    break;
                case NodeTypeEnum::AERATION_TANK:
                case NodeTypeEnum::SEDIMENTATION_TANK:
                case NodeTypeEnum::DIGESTION_TANK:
                    $this->handleTank($node);
                    break;
            }
        }
    }

    private function handleValve(Node $node) : bool {

        if (!$this->isNodeEnabled($node)) {
            return false;
        }

        if ($node->settings->where('name','opened')->first()?->value() === 0) {
            $this->disablePath($node->_lft, $node->_rgt);
            return false;
        }
        return true;
    }

    private function handleTank(Node $node) : bool {

        $capacity = $node->settings()->where('name','capacity')->first();

        $level = Datapoint::where('node_id',$node->id)
            ->where('metric_id',$this->metrics['wl'])
            ->orderBy('id','DESC')
            ->limit(1)
            ->first()
            ->value ?? 0;

        $isNextValveOpen = ($node->children()->first()->settings()
            ->where('name','opened')->first()?->value ?? 0) > 0;

        if ($this->isNodeEnabled($node) && !$isNextValveOpen && $level < $capacity->value()) {
            $level +=10;
        } else if ($isNextValveOpen && $level > 0){
            $level -= 10;
        }

        if ($level < 0) {
            $level = 0;
        } else if ($level > $capacity->value()) {
            $level = $capacity->value();
        }

        Datapoint::create([
            'node_id'=>$node->id,
            'metric_id'=> $this->metrics['wl'],
            'value'=>$level]);

        return $capacity->value() === $level;
    }

    private function disablePath(int $lft, int $rgt) : void {
        $this->disabledPaths = array_merge($this->disabledPaths, range($lft, $rgt));
    }

    private function isNodeEnabled(Node $node) : bool {

        if ($node->isTank()) {
            $isPrevValveOpen = ($node->parent->settings()
                    ->where('name', 'opened')->first()?->value ?? 0) > 0;

            $parentTank = $node->parent->parent ?? null;
            $parentTankLevel = null;
            if ($parentTank && $parentTank->isTank()) {
                $parentTankService = new TankService($parentTank);
                $parentTankLevel = $parentTankService->getLevel();
            }

            if ($isPrevValveOpen && $parentTankLevel > 0) {
                return true;
            } else if ($isPrevValveOpen && is_null($parentTankLevel)) {
                return true;
            }
        }

        return !(in_array($node->_lft, $this->disabledPaths) && in_array($node->_rgt, $this->disabledPaths));
    }
}
