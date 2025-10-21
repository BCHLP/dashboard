<?php

use App\Models\MqttAudit;

it('can store mqtt audits', function () {

    $createServer = app(\App\Actions\CreateServer::class);
    $result = $createServer('broker');
    $mqttBroker = $result['server'];
    $token = $result['token'];

    $this->actingAs($mqttBroker);

    expect(MqttAudit::query()->count())->toBe(0);

    $this->postJson(route('api.mqtt.audits'), [
        [
            'clientId' => 'Client',
            'when' => now()->toIso8601String(),
            'unusual' => false,
            'message' => 'testing',
        ],
    ])->assertStatus(200);

    expect(MqttAudit::query()->count())->toBe(1);
});
