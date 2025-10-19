<?php

namespace Database\Seeders;

use App\Enums\MetricAliasEnum;
use App\Enums\NodeTypeEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\MqttAudit;
use App\Models\Node;
use App\Models\UserFingerprint;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MqttAuditSeeder extends Seeder
{
    public function run(): void
    {
        $sensor = Node::create(['name' => 'SEN02', 'node_type' => NodeTypeEnum::SENSOR]);

        $startDate = Carbon::now()->subDays(8);

        $metrics = Metric::whereIn('alias', [MetricAliasEnum::MQTT_CONNECTED, MetricAliasEnum::MQTT_DISCONNECTED,
            MetricAliasEnum::MQTT_PUBLISHED, MetricAliasEnum::MQTT_SUBSCRIBED])->get();

        $connected = $metrics->where('alias', MetricAliasEnum::MQTT_CONNECTED)->first();
        $disconnected = $metrics->where('alias', MetricAliasEnum::MQTT_DISCONNECTED)->first();
        $published = $metrics->where('alias', MetricAliasEnum::MQTT_PUBLISHED)->first();
        $subscribed = $metrics->where('alias', MetricAliasEnum::MQTT_SUBSCRIBED)->first();

        for ($day = 1; $day <= 7; $day++) {
            $startDate->addDay();
            for ($hour = 7; $hour < 21; $hour++) {

                $startDate->setHour($hour);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'when' => $startDate->format('Y-m-d H:i:s'),
                    'unusual' => false,
                    'message' => 'Valid certificate'
                ]);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'when' => $startDate->format('Y-m-d H:i:s'),
                    'unusual' => false,
                    'message' => 'Allowing connection'
                ]);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'when' => $startDate->format('Y-m-d H:i:s'),
                    'unusual' => false,
                    'message' => 'Client connected'
                ]);

                Datapoint::create([
                    'source_id' => $sensor->id,
                    'source_type' => Node::class,
                    'metric_id' => $connected->id,
                    'time' => $startDate->timestamp,
                    'created_at' => $startDate,
                    'updated_at' => $startDate,
                    'value' => 1
                ]);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'message' => json_encode(['client_id' => $sensor->id, 'wl' => 5, 'fr' => 7]),
                    'unusual' => false,
                    'when' => Carbon::now(),
                ]);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'message' => "Published on metric/send",
                    'unusual' => false,
                    'when' => Carbon::now(),
                ]);

                Datapoint::create([
                    'source_id' => $sensor->id,
                    'source_type' => Node::class,
                    'metric_id' => $published->id,
                    'time' => $startDate->timestamp,
                    'created_at' => $startDate,
                    'updated_at' => $startDate,
                    'value' => 1
                ]);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'message' => "Client disconnected",
                    'unusual' => false,
                    'when' => Carbon::now(),
                ]);

                Datapoint::create([
                    'source_id' => $sensor->id,
                    'source_type' => Node::class,
                    'metric_id' => $disconnected->id,
                    'time' => $startDate->timestamp,
                    'created_at' => $startDate,
                    'updated_at' => $startDate,
                    'value' => 1
                ]);
            }
        }
    }
}
