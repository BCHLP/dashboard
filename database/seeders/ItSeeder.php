<?php

namespace Database\Seeders;

use App\Enums\NodeTypeEnum;
use App\Models\Node;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItSeeder extends Seeder
{
    public function run(): void
    {
        $ai = Node::factory(['name' => 'AI', 'node_type' => NodeTypeEnum::SERVER])->create();

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\Models\Node',
            'tokenable_id' => $ai->id,
            'name' => 'AI',
            'token' => '7b155a576de0e4245b458216ab024c6b4aa2207ddf51581867acffea8603a5a9',
            'abilities' => '["*"]',
        ]);
    }
}
