<?php

namespace App\Listeners;

use App\Enums\MetricAliasEnum;
use App\Enums\NodeTypeEnum;
use App\Events\NodeCreatedEvent;
use App\Models\Datapoint;
use App\Models\Node;
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
                $event->node->metrics()->sync([
                    $metrics[MetricAliasEnum::WATER_LEVEL->value],
                    $metrics[MetricAliasEnum::WATER_TEMPERATURE->value],
                    $metrics[MetricAliasEnum::PH_LEVEL->value],
                    $metrics[MetricAliasEnum::GPS_LAT->value],
                    $metrics[MetricAliasEnum::GPS_LNG->value]
                    ]);


                Datapoint::createQuietly(['time' => time(),
                    'source_id' => $event->node->id,
                    'source_type' => Node::class,
                    'metric_id' => $metrics[MetricAliasEnum::WATER_LEVEL->value], 'value' => 80]);

                Datapoint::createQuietly(['time' => time(),
                    'source_id' => $event->node->id,
                    'source_type' => Node::class,
                    'metric_id' => $metrics[MetricAliasEnum::GPS_LNG->value], 'value' => 115.770545]);

                Datapoint::createQuietly(['time' => time(),
                    'source_id' => $event->node->id,
                    'source_type' => Node::class,
                    'metric_id' => $metrics[MetricAliasEnum::GPS_LAT->value], 'value' => -31.743739]);

                break;

            case NodeTypeEnum::SENSOR:
                $event->node->metrics()->sync([
                    $metrics[MetricAliasEnum::MQTT_CONNECTED->value],
                    $metrics[MetricAliasEnum::MQTT_DISCONNECTED->value],
                    $metrics[MetricAliasEnum::MQTT_PUBLISHED->value],
                    $metrics[MetricAliasEnum::MQTT_SUBSCRIBED->value]
                ]);

        }
    }
}
