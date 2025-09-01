<?php

namespace Database\Seeders;

use App\Models\DeviceMetric;
use App\Models\Metric;
use App\Models\Router;
use App\Models\Server;
use Illuminate\Database\Seeder;

class ItSeeder extends Seeder
{
    public function run(): void
    {
        $scada = Server::factory(['name' => 'SCADA'])->create();
        $mqtt = Server::factory(['name' => 'MQTT Proxy'])->create();
        $router = Router::factory(['name' => 'Router'])->create();

        $cpu = Metric::create([
            'name' => 'cpu',
            'alias' => 'cpu',
            'baseline_minimum' => 0,
            'baseline_maximum' => 100
        ]);

        $memory = Metric::create([
            'name' => 'memory',
            'alias' => 'memory',
            'baseline_minimum' => 0,
            'baseline_maximum' => 100
        ]);

        $networkI = Metric::create([
            'name' => 'network_i',
            'alias' => 'network_i',
            'baseline_minimum' => 0,
            'baseline_maximum' => 100
        ]);

        $networkO = Metric::create([
            'name' => 'network_o',
            'alias' => 'network_ox',
            'baseline_minimum' => 0,
            'baseline_maximum' => 100
        ]);

        foreach([$scada, $mqtt, $router] as $device) {

            DeviceMetric::create([
                'metric_id' => $cpu->id,
                'device_metric_id' => $device->id,
                'device_metric_type' => Server::class
            ]);

            DeviceMetric::create([
                'metric_id' => $memory->id,
                'device_metric_id' => $device->id,
                'device_metric_type' => Server::class
            ]);

            DeviceMetric::create([
                'metric_id' => $networkI->id,
                'device_metric_id' => $device->id,
                'device_metric_type' => Server::class
            ]);

            DeviceMetric::create([
                'metric_id' => $networkO->id,
                'device_metric_id' => $device->id,
                'device_metric_type' => Server::class
            ]);
        }
    }
}
