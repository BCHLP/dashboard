<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Simps\MQTT\Client;
use Swoole\Coroutine;
use Simps\MQTT\Config\ClientConfig;

class MqttServerCommand extends Command
{
    protected $signature = 'mqtt:server';

    protected $description = 'Start the MQTT server';

    public function handle(): void
    {
        Coroutine\run(function () {
            $client = new Client(config('mqtt.host'), config('mqtt.port'), $this->getTestMQTT5ConnectConfig());
            $client->connect();
            while (true) {
                $response = $client->publish(
                    'simps-mqtt/user001/update',
                    '{"time":' . time() . '}',
                    1,
                    0,
                    0,
                    [
                        'topic_alias' => 1,
                        'message_expiry_interval' => 12,
                    ]
                );
                var_dump($response);
                Coroutine::sleep(3);
            }
        });
    }

    private function getTestConnectConfig()
    {
        $config = new ClientConfig();

        return $config->setUserName('')
            ->setPassword('')
            ->setClientId(Client::genClientID())
            ->setKeepAlive(10)
            ->setDelay(3000) // 3s
            ->setMaxAttempts(5)
            ->setSwooleConfig(SWOOLE_MQTT_CONFIG);
    }

    private function getTestMQTT5ConnectConfig()
    {
        $config = new ClientConfig();

        return $config->setUserName('')
            ->setPassword('')
            ->setClientId(Client::genClientID())
            ->setKeepAlive(10)
            ->setDelay(3000) // 3s
            ->setMaxAttempts(5)
            ->setProperties([
                'session_expiry_interval' => 60,
                'receive_maximum' => 65535,
                'topic_alias_maximum' => 65535,
            ])
            ->setProtocolLevel(5)
            ->setSwooleConfig([
                'open_mqtt_protocol' => true,
                'package_max_length' => 2 * 1024 * 1024,
                'connect_timeout' => 5.0,
                'write_timeout' => 5.0,
                'read_timeout' => 5.0,
            ]);
    }
}
