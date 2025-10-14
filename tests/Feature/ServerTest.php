<?php


use App\Actions\CreateServer;
use App\Models\Metric;
use App\Models\Node;

test('CreateServer creates a server', function () {

    $createServer = app(CreateServer::class);
    $result = $createServer("server");
    $server = $result['server'];
    $token = $result['token'];

    expect(get_class($server))->toBe(Node::class)
        ->and($server->metrics)->toHaveCount(5)
        ->and($server->metrics->where('alias', \App\Enums\MetricAliasEnum::CPU))->not->toBeNull();
});
