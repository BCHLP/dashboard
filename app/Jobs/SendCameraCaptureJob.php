<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpMqtt\Client\MqttClient;

class SendCameraCaptureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(): void
    {
        $server = config('scada.mqtt_broker.host');
        $port = config('scada.mqtt_broker.port');
        $clientId = 'queue';

        $certPath = storage_path('certs/');

        $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
            ->setUseTls(true)
            ->setTlsVerifyPeer(true)
            ->setTlsVerifyPeerName(false)
            ->setTlsCertificateAuthorityFile($certPath.'ca-chain.crt')
            ->setTlsClientCertificateFile($certPath.'bchklp.crt')  // PEM format
            ->setTlsClientCertificateKeyFile($certPath.'bchklp.key')  // PEM format
            ->setTlsSelfSignedAllowed(false);

        $mqtt = new MqttClient($server, $port, $clientId);
        $mqtt->connect($connectionSettings);
        $mqtt->publish('application/1/device/SEN-001/down', json_encode(['capture' => 'camera']), 0);
    }
}
