<?php


use App\Enums\NodeTypeEnum;
use App\Models\MqttAudit;
use App\Models\Node;

it('can store mqtt audits', function () {

    $mqttBroker = Node::factory(['node_type' => NodeTypeEnum::SERVER])->create();
    $token = $mqttBroker->createToken("mqttBroker");

    $this->actingAs($mqttBroker);

    expect(MqttAudit::query()->count())->toBe(0);

    $response = $this->postJson(route('api.mqtt.audits'), [
        'audits' => [
            [
                'client_id' => "Client",
                "when" => now()->toIso8601String(),
                "unusual" => false,
                "message" => "testing"
            ]
        ]
    ]);

    expect(MqttAudit::query()->count())->toBe(1);

    $response->assertStatus(200);
});
