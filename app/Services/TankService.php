<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TankService
{
    private ?Node $tank;

    private array $metrics = [];
    public function __construct(Node|string $node) {
        if (is_string($node)) {
            $this->tank = Node::findByName($node);
            return;
        }
        $this->tank = $node;

        $this->metrics = MetricService::getMetricKeys();
    }

    private ?int $capacity = null;
    public function getCapacity(): int {
        if (blank($this->capacity)){
            $this->capacity = $this->tank->settings()->where('name', 'capacity')->first()->value() ?? 0;
        }
        return $this->capacity;
    }


    private ?int $levelPercentage = null;
    public function getLevelPercentage(): int {

        if (!filled($this->levelPercentage)) {


            $level = $this->getLevel();
            $capacity = $this->getCapacity();
            if ($level > 0 && $capacity > 0) {
                $this->levelPercentage = intval((($level / $capacity) * 100));
            } else {
                $this->levelPercentage = 0;
            }
        }

        return $this->levelPercentage;

    }

    private ?int $level = null;
    public function getLevel(): int {

        if (!filled($this->level)) {

            $this->level = intval(Datapoint::where('node_id', $this->tank->id)
                ->where('metric_id', $this->metrics['wl'])
                ->orderBy('id', 'desc')
                ->limit(1)
                ->first()
                ->value ?? 0);

        }

        return $this->level;

    }

    public function filled() : bool {
        return ($this->getLevel() === $this->getCapacity());
    }

    public function reset() : self {
        $this->level = null;
        $this->levelPercentage = null;
        return $this;
    }

    public function setLevel(int $level) {
        Datapoint::create([
            'node_id'=>$this->tank->id,
            'metric_id'=> $this->metrics['wl'],
            'value'=>$level]);
    }
}
