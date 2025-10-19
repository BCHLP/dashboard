<?php
declare(strict_types=1);

namespace App\Enums;

enum MetricAliasEnum: string
{
    CASE WATER_TEMPERATURE = 'temp';
    CASE PH_LEVEL = 'pH';
    CASE PRESSURE = 'MPa';
    CASE ORB = 'mV';

    case GPS_LAT = 'lat';
    case GPS_LNG = 'lng';

    case CAMERA = 'camera';

    case CPU = 'cpu';
    case NETWORK_BYTES_IN = 'network_bytes_in';
    case NETWORK_BYTES_OUT = 'network_bytes_out';
    case NETWORK_PACKETS_IN = 'network_packets_in';
    case NETWORK_PACKETS_OUT = 'network_packets_out';

    case MQTT_CONNECTED = 'mqtt_connected';
    case MQTT_PUBLISHED = 'mqtt_published';
    case MQTT_SUBSCRIBED = 'mqtt_subscribed';
    case MQTT_DISCONNECTED = 'mqtt_disconnected';


    case USER_AUTH_FAILED = 'user_auth_failed';
    case USER_AUTH_SUCCESSFUL = 'user_auth_successful';


}
