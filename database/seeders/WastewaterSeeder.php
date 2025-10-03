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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class WastewaterSeeder extends Seeder
{
    public function run(): void
    {

        $inlet = Node::create(['name' => "Inlet", 'node_type' => NodeTypeEnum::INLET]);
        $flowRate = Metric::create(['name' => 'Flow Rate', 'alias' => MetricAliasEnum::FLOW_RATE]);
        $waterLevel = Metric::create(['name' => 'Water Level', 'alias' => MetricAliasEnum::WATER_LEVEL]);

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

        foreach(['A','B','C'] as $letter) {

            $line = TreatmentLine::create([
                'name' => $letter,
                'maintenance_mode' => false,
                'stage_1' => ($letter === 'A' ? TreatmentStageEnum::FILLING : TreatmentStageEnum::AVAILABLE),
            ]);

            $valve0 = Node::create(['name' => "VAL-{$letter}0", 'node_type' => NodeTypeEnum::VALVE, 'treatment_line_id' => $line->id], $inlet);

            $screen = Node::create(['name' => "SCR-{$letter}0", 'node_type' => NodeTypeEnum::SCREEN, 'treatment_line_id' => $line->id], $valve0);

            $valve1 = Node::create(['name' => "VAL-{$letter}1", 'node_type' => NodeTypeEnum::VALVE, 'treatment_line_id' => $line->id], $screen);
            $tank1 = Node::create(['name' => "SED-{$letter}1", 'node_type' => NodeTypeEnum::SEDIMENTATION_TANK, 'treatment_line_id' => $line->id], $valve1);

            $valve2 = Node::create(['name' => "VAL-{$letter}2", 'node_type' => NodeTypeEnum::VALVE, 'treatment_line_id' => $line->id], $tank1);
            $tank2 = Node::create(['name' => "AER-{$letter}2", 'node_type' => NodeTypeEnum::AERATION_TANK, 'treatment_line_id' => $line->id], $valve2);

            $valve3 = Node::create(['name' => "VAL-{$letter}3", 'node_type' => NodeTypeEnum::VALVE, 'treatment_line_id' => $line->id], $tank2);
            $tank3 = Node::create(['name' => "SED-{$letter}3", 'node_type' => NodeTypeEnum::SEDIMENTATION_TANK, 'treatment_line_id' => $line->id], $valve3);

            $valve4 = Node::create(['name' => "VAL-{$letter}4", 'node_type' => NodeTypeEnum::VALVE, 'treatment_line_id' => $line->id], $tank3);
            $pump = Node::create(['name' => "PUMP-{$letter}", 'node_type' => NodeTypeEnum::PUMP, 'treatment_line_id' => $line->id], $valve4);
            $outlet = Node::create(['name' => "Outlet-{$letter}", 'node_type' => NodeTypeEnum::OUTLET, 'treatment_line_id' => $line->id], $pump);
        }

        $valvesToKeepOpen = Node::whereIn('name', ['VAL-A0','VAL-A1'])->pluck('id');

        NodeSetting::where('name', 'opened')
            ->whereNotIn('node_id', $valvesToKeepOpen)
            ->update(['value' =>  '0']);

    }
}
