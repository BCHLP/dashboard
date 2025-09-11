<?php
return [
    'services' => [
        'voice' => [
            'register' => env('VOICE_REGISTER', ''),
            'compare' => env('VOICE_COMPARE', ''),
            'token' => env('VOICE_TOKEN', ''),
        ]
    ]
];
