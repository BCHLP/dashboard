<?php

namespace Database\Seeders;

use App\Enums\NodeTypeEnum;
use App\Enums\TreatmentStageEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Node;
use App\Models\NodeSetting;
use App\Models\NodeConnection;
use App\Models\TreatmentLine;
use Illuminate\Database\Seeder;

class WastewaterSeeder extends Seeder
{
    public function run(): void
    {
        $inlet = Node::create(['name' => "Inlet", 'node_type' => NodeTypeEnum::INLET]);
        $flowRate = Metric::create(['name' => 'Flow Rate', 'alias' => 'fr']);
        $waterLevel = Metric::create(['name' => 'Water Level', 'alias' => 'wl']);

        foreach(['A','B','C'] as $letter) {

            $valve0 = Node::create(['name' => "VAL-{$letter}0", 'node_type' => NodeTypeEnum::VALVE], $inlet);

            $screen = Node::create(['name' => "SCR-{$letter}0", 'node_type' => NodeTypeEnum::SCREEN], $valve0);

            $valve1 = Node::create(['name' => "VAL-{$letter}1", 'node_type' => NodeTypeEnum::VALVE], $screen);
            $tank1 = Node::create(['name' => "SED-{$letter}1", 'node_type' => NodeTypeEnum::SEDIMENTATION_TANK], $valve1);

            $valve2 = Node::create(['name' => "VAL-{$letter}2", 'node_type' => NodeTypeEnum::VALVE], $tank1);
            $tank2 = Node::create(['name' => "AER-{$letter}2", 'node_type' => NodeTypeEnum::AERATION_TANK], $valve2);

            $valve3 = Node::create(['name' => "VAL-{$letter}3", 'node_type' => NodeTypeEnum::VALVE], $tank2);
            $tank3 = Node::create(['name' => "SED-{$letter}3", 'node_type' => NodeTypeEnum::SEDIMENTATION_TANK], $valve3);

            $valve4 = Node::create(['name' => "VAL-{$letter}4", 'node_type' => NodeTypeEnum::VALVE], $tank3);
            $pump = Node::create(['name' => "PUMP-{$letter}", 'node_type' => NodeTypeEnum::PUMP], $valve4);
            $outlet = Node::create(['name' => "Outlet-{$letter}", 'node_type' => NodeTypeEnum::OUTLET], $pump);



            foreach ([$valve0, $valve1, $valve2, $valve3, $valve4] as $valve) {
                NodeSetting::create(['node_id' => $valve->id, 'name' => 'opened', 'value' => '100', 'cast' => 'int']);
                $valve->metrics()->sync([$flowRate]);
            }

            foreach ([$tank1, $tank2, $tank3] as $tank) {
                NodeSetting::create(['node_id' => $tank->id, 'name' => 'capacity', 'value' => '100.0', 'cast' => 'float']);
                NodeSetting::create(['node_id' => $tank->id, 'name' => 'filled_time', 'value' => '0', 'cast' => 'int']);
                $tank->metrics()->sync([$waterLevel]);
            }

            Datapoint::create(['node_id' => $tank1->id, 'metric_id' => $waterLevel->id, 'value' => 0]);
            Datapoint::create(['node_id' => $tank2->id, 'metric_id' => $waterLevel->id, 'value' => 0]);
            Datapoint::create(['node_id' => $tank3->id, 'metric_id' => $waterLevel->id, 'value' => 0]);

            TreatmentLine::create([
                'name' => $letter,
                'start_node_id' => $valve0->id,
                'end_node_id' => $outlet->id,
                'maintenance_mode' => false,
                'stage' => TreatmentStageEnum::TANK1_FILLING,
            ]);
        }
    }
}
