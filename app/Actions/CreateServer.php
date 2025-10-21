<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\MetricAliasEnum;
use App\Enums\NodeTypeEnum;
use App\Models\Metric;
use App\Models\Node;

class CreateServer
{
    public function __invoke(string $name)
    {

        $server = Node::create([
            'name' => $name,
            'node_type' => NodeTypeEnum::SERVER,
        ]);

        $token = $server->createToken('API');

        $metrics = Metric::whereIn('alias', [
            MetricAliasEnum::CPU,
            MetricAliasEnum::NETWORK_BYTES_IN,
            MetricAliasEnum::NETWORK_BYTES_OUT,
            MetricAliasEnum::NETWORK_PACKETS_IN,
            MetricAliasEnum::NETWORK_PACKETS_OUT,
        ])->pluck('id')->toArray();

        $server->metrics()->sync($metrics);

        return [
            'server' => $server,
            'token' => $token,
        ];
    }
}
