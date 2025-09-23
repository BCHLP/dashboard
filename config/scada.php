<?php
return [
    'services' => [
        'voice' => [
            'register' => env('VOICE_REGISTER', ''),
            'compare' => env('VOICE_COMPARE', ''),
            'token' => env('VOICE_TOKEN', ''),
        ],
        'adaptive_mfa_endpoint' => env('ADAPTIVE_MFA_ENDPOINT', ''),
    ]
];
