<?php

namespace App\Console\Commands;

use App\Events\MqttHardwareEvent;
use App\Models\Datapoint;
use App\Models\DeviceMetric;
use App\Models\Metric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use PhpMqtt\Client\Exceptions\ConfigurationInvalidException;
use PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException;
use PhpMqtt\Client\Exceptions\DataTransferException;
use PhpMqtt\Client\Exceptions\ProtocolNotSupportedException;
use PhpMqtt\Client\Exceptions\RepositoryException;
use PhpMqtt\Client\MqttClient;
use Laravel\Sanctum\PersonalAccessToken;

class MqttCommand extends Command
{
    protected $signature = 'mqtt';

    protected $description = 'Command description';

    /**
     * @throws ConfigurationInvalidException
     * @throws ConnectingToBrokerFailedException
     * @throws RepositoryException
     * @throws ProtocolNotSupportedException
     * @throws DataTransferException
     */
    public function handle(): void
    {
        $server   = '0.0.0.0';
        $port     = 1883;
        $clientId = 'test-publisher';

        $metrics = Metric::all();

        $mqtt = new MqttClient($server, $port, $clientId);
        $mqtt->connect();
        // $mqtt->publish('php-mqtt/client/test', 'Hello World!', 0);
        $mqtt->subscribe('hardware-metrics', function ($topic, $message, $retained, $matchedWildcards) use ($metrics) {
            $this->info("Topic: $topic");
            $payload = json_decode($message, true);
            if (is_null($payload)) {
                $this->error("Bad payload received:  {$message}");
                return;
            }

            if (!isset($payload['token'], $payload['cpu'], $payload['ram'], $payload['load_avg'])) {
                $this->error("Bad JSON received");
                return;
            }

            $token = Cache::remember('users', 3600, function () use ($payload) {
                return PersonalAccessToken::findToken($payload['token']);
            });


            if (blank($token)) {
                $this->error("Token not found");
                return;
            }

            $cpu = $metrics->where('alias', 'cpu')->first();
            $memory = $metrics->where('alias', 'ram')->first();

            if (!$cpu || !$memory) {
                $this->error("CPU or MEMORY metrics not found");
                return;
            }


            Datapoint::create([
                'metric_id' => $cpu->id,
                'node_id' => $token->tokenable_id,
                'value' => $payload['cpu']

            ]);

            Datapoint::create([
                'metric_id' => $memory->id,
                'node_id' => $token->tokenable_id,
                'value' => $payload['ram']

            ]);


        }, 0);
        $mqtt->loop(true);
        $mqtt->disconnect();
    }
}
