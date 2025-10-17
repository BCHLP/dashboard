<?php

namespace App\Console\Commands;

use App\Enums\MetricAliasEnum;
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
        $server   = config('scada.mqtt_broker.host');
        $port     = config('scada.mqtt_broker.port');
        $clientId = 'laravel';

        $certPath = storage_path('certs/');

        $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
            ->setUseTls(true)
            ->setTlsVerifyPeer(true)
            ->setTlsVerifyPeerName(false)
            ->setTlsCertificateAuthorityFile($certPath . "ca-chain.crt")
            ->setTlsClientCertificateFile($certPath . "bchklp.crt")  // PEM format
            ->setTlsClientCertificateKeyFile($certPath . "bchklp.key")  // PEM format
            ->setTlsSelfSignedAllowed(false);

        $mqtt = new MqttClient($server, $port, $clientId);
        $mqtt->connect($connectionSettings);
        // $mqtt->publish('php-mqtt/client/test', 'Hello World!', 0);

        $mqtt->subscribe("application/+/device/+/command/+", function(string $topic, string $message,
                                                                      bool $retained, array $wildcards) {

            $this->info("Topic: $topic");
            $this->info("Message: $message");
            $this->info("Wildcards: " . json_encode($wildcards));

            $json = json_decode($message, true);

            $a = MqttAudit::create([
                'client_id' => $json['id'] ?? "Unknown",
                'message' => $message,
                'unusual' => false,
                'when' => Carbon::now(),
            ]);


            if ($json === null) {
                return;
            }

            $sensor = Node::with('metrics')->where('name',$json['id'])->first();
            if (!$sensor) {
                return;
            }

            $metrics = $sensor->metrics;
            foreach($json as $metricKey => $metricValue) {
                if ($metricKey === 'id') {
                    continue;
                }

                if ($metricKey === 'gps') {
                    // gps is handled slightly differently


                    $lat = $metrics->where('alias', MetricAliasEnum::GPS_LAT->value)->first();
                    $lng = $metrics->where('alias', MetricAliasEnum::GPS_LNG->value)->first();

                    $d1 = Datapoint::create([
                        'source_id' => $sensor->id,
                        'source_type' => Node::class,
                        'metric_id' => $lat->id,
                        'value' => $metricValue['lat'],
                        'time' => time()
                    ]);

                    $d2 = Datapoint::create([
                        'source_id' => $sensor->id,
                        'source_type' => Node::class,
                        'metric_id' => $lng->id,
                        'value' => $metricValue['lng'],
                        'time' => time()
                    ]);

                    continue;
                }

                $metric = $metrics->where('alias', $metricKey)->first();

                if ($metric) {
                    Datapoint::create([
                        'source_id' => $sensor->id,
                        'source_type' => Node::class,
                        'metric_id' => $metric->id,
                        'value' => $metricValue,
                        'time' => time()
                    ]);
                }
            }
        });

        $mqtt->loop(true);
        $mqtt->disconnect();
    }
}
