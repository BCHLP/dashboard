<?php

namespace Database\Seeders;

use App\Enums\NodeTypeEnum;
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
        $sensor = Node::factory(['node_type' => NodeTypeEnum::SENSOR])->create();

        $startDate = Carbon::now()->subDays(8);

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

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'message' => json_encode(['client_id' => $sensor->id, 'wl' => 5, 'fr => 7']),
                    'unusual' => false,
                    'when' => Carbon::now(),
                ]);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'message' => "Published on metric/send",
                    'unusual' => false,
                    'when' => Carbon::now(),
                ]);

                MqttAudit::create([
                    'client_id' => $sensor->id,
                    'message' => "Client disconnected",
                    'unusual' => false,
                    'when' => Carbon::now(),
                ]);
            }
        }
    }
}
