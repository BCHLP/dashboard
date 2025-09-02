<?php

namespace Database\Seeders;

use App\Enums\NodeTypeEnum;
use App\Models\DeviceMetric;
use App\Models\Metric;
use App\Models\Router;
use App\Models\Server;
use Illuminate\Database\Seeder;
use App\Models\Node;
class ItSeeder extends Seeder
{
    public function run(): void
    {
        $scada = Node::factory(['name' => 'SCADA', 'node_type' => NodeTypeEnum::SERVER])->create();
        $mqtt = Node::factory(['name' => 'MQTT Proxy', 'node_type' => NodeTypeEnum::SERVER])->create();
        $router = Node::factory(['name' => 'Router', 'node_type' => NodeTypeEnum::ROUTER])->create();
        $sensor = Node::factory(['name' => 'SEN001', 'node_type' => NodeTypeEnum::SENSOR])->create();

        $cpu = Metric::create([
            'name' => 'CPU',
            'alias' => 'cpu',
        ]);

        $memory = Metric::create([
            'name' => 'Memory',
            'alias' => 'ram',
        ]);

        foreach([$scada, $mqtt, $router] as $device) {
            $device->metrics()->attach($cpu);
            $device->metrics()->attach($memory);
        }
    }
}
