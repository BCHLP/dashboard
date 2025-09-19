<?php

namespace App\Listeners;

use App\Enums\NodeTypeEnum;
use App\Events\NodeCreatedEvent;
use App\Models\Datapoint;
use App\Models\NodeSetting;
use App\Services\MetricService;

class ConfigureNodeListener
{
    public function __construct()
    {
    }

    public function handle(NodeCreatedEvent $event): void
    {
        $metrics = MetricService::getMetricKeys();

        switch($event->node->node_type) {
            case NodeTypeEnum::VALVE:
                NodeSetting::create(['node_id' => $event->node->id, 'name' => 'opened', 'value' => '100', 'cast' => 'int']);
                $event->node->metrics()->sync([$metrics['fr']]);

                break;

            case NodeTypeEnum::SEDIMENTATION_TANK:
            case NodeTypeEnum::AERATION_TANK:
            case NodeTypeEnum::DIGESTION_TANK:
                NodeSetting::create(['node_id' => $event->node->id, 'name' => 'capacity', 'value' => '100', 'cast' => 'int']);
                NodeSetting::create(['node_id' => $event->node->id, 'name' => 'filled_time', 'value' => '0', 'cast' => 'int']);
                $event->node->metrics()->sync([$metrics['wl']]);
                Datapoint::create(['time' => time(), 'node_id' => $event->node->id, 'metric_id' => $metrics['wl'], 'value' => 0]);

                break;

        }
    }
}
