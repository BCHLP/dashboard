<?php
return [
    'services' => [
        'voice' => [
            'register' => env('VOICE_REGISTER', ''),
            'compare' => env('VOICE_COMPARE', ''),
            'token' => env('VOICE_TOKEN', ''),
        ],
        'adaptive_mfa_endpoint' => env('ADAPTIVE_MFA_ENDPOINT', ''),
    ],
    'mqtt_broker' => [
        'host' => env('MQTT_HOST', 'localhost'),
        'port' => env('MQTT_PORT', 8883),
    ],
    'amfa' => [
        'enabled' => env('AMFA_ENABLED', true),
    ]
];
