<?php

namespace Database\Seeders;

use App\Enums\MetricAliasEnum;
use App\Enums\NodeTypeEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $sensor = Node::create(['name' => 'SEN-001', 'node_type' => NodeTypeEnum::SENSOR]);

        $metrics = [];
        $metrics[] = Metric::create(['name' => 'Water Temperature', 'alias' => MetricAliasEnum::WATER_TEMPERATURE]);
        $metrics[] = Metric::create(['name' => 'pH Level', 'alias' => MetricAliasEnum::PH_LEVEL]);
        $metrics[] = Metric::create(['name' => 'Latitude', 'alias' => MetricAliasEnum::GPS_LAT]);
        $metrics[] = Metric::create(['name' => 'Longitude', 'alias' => MetricAliasEnum::GPS_LNG]);
        $metrics[] = Metric::create(['name' => 'Camera', 'alias' => MetricAliasEnum::CAMERA]);

        foreach ($metrics as $metric) {
            $sensor->metrics()->attach($metric);
        }

        Metric::create(['name' => 'CPU', 'alias' => MetricAliasEnum::CPU]);
        Metric::create(['name' => 'Network Packets In', 'alias' => MetricAliasEnum::NETWORK_PACKETS_IN]);
        Metric::create(['name' => 'Network Packets Out', 'alias' => MetricAliasEnum::NETWORK_PACKETS_OUT]);
        Metric::create(['name' => 'Network Bytes In', 'alias' => MetricAliasEnum::NETWORK_BYTES_IN]);
        Metric::create(['name' => 'Network Bytes Out', 'alias' => MetricAliasEnum::NETWORK_BYTES_OUT]);

        Metric::create(['name' => 'MQTT Connected', 'alias' => MetricAliasEnum::MQTT_CONNECTED]);
        Metric::create(['name' => 'MQTT Subscribed', 'alias' => MetricAliasEnum::MQTT_SUBSCRIBED]);
        Metric::create(['name' => 'MQTT Published', 'alias' => MetricAliasEnum::MQTT_PUBLISHED]);
        Metric::create(['name' => 'MQTT Disconnected', 'alias' => MetricAliasEnum::MQTT_DISCONNECTED]);

        Metric::create(['name' => 'User Authentication Failed', 'alias' => MetricAliasEnum::USER_AUTH_FAILED]);
        Metric::create(['name' => 'User Authentication Successful', 'alias' => MetricAliasEnum::USER_AUTH_SUCCESSFUL]);

        Cache::clear();
    }

    private function createHistory(Node $node, Metric $metric, int $min, int $max): void
    {
        $startDate = Carbon::now()->subHours(72)->setMinutes(0)->setSeconds(0);
        $endDate = Carbon::now();
        $totalMinutes = round($startDate->diffInMinutes($endDate));

        $value = $min;
        $direction = 'up';

        for ($minute = 0; $minute <= $totalMinutes; $minute++) {
            $startDate->addMinute();

            if ($value < $max && $direction === 'up') {
                $value++;
            } elseif ($value > $min && $direction === 'down') {
                $value--;
            } elseif ($value === $max && $direction === 'up') {
                $value--;
                $direction = 'down';
            } elseif ($value === $min && $direction === 'down') {
                $value++;
                $direction = 'up';
            }

            Datapoint::create([
                'metric_id' => $metric->id,
                'source_id' => $node->id,
                'source_type' => Node::class,
                'created_at' => $startDate,
                'updated_at' => $startDate,
                'time' => $startDate->timestamp,
                'value' => $value,
            ]);
        }
    }
}
