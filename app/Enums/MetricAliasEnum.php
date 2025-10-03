<?php
declare(strict_types=1);

namespace App\Enums;

enum MetricAliasEnum: string
{
    case FLOW_RATE = 'fr';
    case WATER_LEVEL = 'wl';
    case CPU = 'cpu';
    case NETWORK_BYTES_IN = 'network_bytes_in';
    case NETWORK_BYTES_OUT = 'network_bytes_out';
    case NETWORK_PACKETS_IN = 'network_packets_in';
    case NETWORK_PACKETS_OUT = 'network_packets_out';

    case MQTT_CONNECTED = 'mqtt_connected';
    case MQTT_PUBLISHED = 'mqtt_published';
    case MQTT_SUBSCRIBED = 'mqtt_subscribed';
    case MQTT_DISCONNECTED = 'mqtt_disconnected';


}
