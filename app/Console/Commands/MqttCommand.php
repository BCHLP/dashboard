<?php

namespace App\Console\Commands;

use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\MqttAudit;
use App\Models\Node;
use Carbon\Carbon;
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
        $server   = 'localhost';
        $port     = 8883;
        $clientId = 'laravel';

        $metrics = Metric::all();

        $certPath = storage_path('certs/');

        $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
            ->setUseTls(true)
            ->setTlsVerifyPeer(true)
            // ->setTlsVerifyPeerName(false)  // May need this for localhost
            ->setTlsCertificateAuthorityFile($certPath . "ca-chain.crt")
            ->setTlsClientCertificateFile($certPath . "laravel.crt")  // PEM format
            ->setTlsClientCertificateKeyFile($certPath . "laravel.key");  // PEM format

        $mqtt = new MqttClient($server, $port, $clientId);
        $mqtt->connect($connectionSettings);
        // $mqtt->publish('php-mqtt/client/test', 'Hello World!', 0);

        $mqtt->subscribe("metric/send", function($topic, $message) {
            $this->info("Topic: $topic");
            $this->info("Message: $message");

            $this->info("About to decode");
            $json = json_decode($message, true);
            $this->info("Decoded message: $message");
            if ($json === null) {
                $this->warn("Failed to decode json");
            }

            $this->info("Creating mqaudit");
            $a = MqttAudit::create([
                'client_id' => $json['client_id'] ?? "Unknown",
                'message' => $message,
                'unusual' => false,
                'when' => Carbon::now(),
            ]);


            if ($json === null) {
                $this->info("Return if json is null");
                return;
            }

            $this->info("Find sensor");
            dd($json['client_id']);
            $sensor = Node::find($json['client_id'] ?? 0);
            if (!$sensor) {
                $this->info("Did not find sensor for " . $json['client_id'] ?? 0);
                return;
            }

            $this->info("get all metrics");

            $metrics = Metric::all();
            foreach($json as $metricKey => $metricValue) {
                $this->line($metricKey);
                $metric = $metrics->where('alias', $metricKey);
                if ($metric->count() === 1) {
                    Datapoint::create([
                        'node_id' => $sensor->id,
                        'metric_id' => $metric->id,
                        'value' => $metricValue,
                        'time' => time()
                    ]);
                }
            }
        });

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
