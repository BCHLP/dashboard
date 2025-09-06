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
use App\Services\MetricService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class WastewaterSeeder extends Seeder
{
    public function run(): void
    {

        $inlet = Node::create(['name' => "Inlet", 'node_type' => NodeTypeEnum::INLET]);
        $flowRate = Metric::create(['name' => 'Flow Rate', 'alias' => 'fr']);
        $waterLevel = Metric::create(['name' => 'Water Level', 'alias' => 'wl']);
        Cache::clear();

        foreach(['A','B','C'] as $letter) {

            $line = TreatmentLine::create([
                'name' => $letter,
                'maintenance_mode' => false,
                'stage' => ($letter === 'A' ? TreatmentStageEnum::TANK1_FILLING : TreatmentStageEnum::AVAILABLE),
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
