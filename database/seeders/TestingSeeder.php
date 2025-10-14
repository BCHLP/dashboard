<?php

namespace Database\Seeders;

use App\Models\Datapoint;
use App\Models\MetricBaseline;
use Illuminate\Database\Seeder;

class TestingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
