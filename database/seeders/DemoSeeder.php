<?php

namespace Database\Seeders;

use App\Enums\MetricAliasEnum;
use App\Enums\NodeTypeEnum;
use App\Enums\TreatmentStageEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;
use App\Models\NodeSetting;
use App\Models\NodeConnection;
use App\Models\TreatmentLine;
use App\Services\MetricService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DemoSeeder extends Seeder
{
    public function run(): void
    {

        $inlet = Node::create(['name' => "Inlet", 'node_type' => NodeTypeEnum::INLET]);
        $flowRate = Metric::create(['name' => 'Flow Rate', 'alias' => MetricAliasEnum::FLOW_RATE]);
        $waterLevel = Metric::create(['name' => 'Water Level', 'alias' => MetricAliasEnum::WATER_LEVEL]);
        $waterTemp = Metric::create(['name' => 'Water Temperature', 'alias' => MetricAliasEnum::WATER_TEMPERATURE]);
        $phLevel = Metric::create(['name' => 'pH Level', 'alias' => MetricAliasEnum::PH_LEVEL]);
        $gpsLat = Metric::create(['name' => 'lat', 'alias' => MetricAliasEnum::GPS_LAT]);
        $gpsLng = Metric::create(['name' => 'lng', 'alias' => MetricAliasEnum::GPS_LNG]);

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

        $tank = Node::create(['name' => "SEN-001", 'node_type' => NodeTypeEnum::SEDIMENTATION_TANK]);

    }

    private function createHistory(Node $node, Metric $metric, int $min, int $max) : void {
        $startDate = Carbon::now()->subHours(72)->setMinutes(0)->setSeconds(0);
        $endDate = Carbon::now();
        $totalMinutes = round($startDate->diffInMinutes($endDate));

        $value = $min;
        $direction = 'up';

        for($minute = 0; $minute <= $totalMinutes; $minute++) {
            $startDate->addMinute();

            if ($value < $max && $direction === 'up') {
                $value++;
            } else if ($value > $min && $direction === 'down') {
                $value--;
            } else if ($value === $max && $direction === 'up') {
                $value--;
                $direction = 'down';
            } else if ($value === $min && $direction === 'down') {
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
