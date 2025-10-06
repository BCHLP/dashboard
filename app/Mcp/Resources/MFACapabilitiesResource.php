<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Server\Resource;

class MFACapabilitiesResource extends Resource
{
    protected string $description = 'Available MFA methods and their characteristics';

    protected string $uri = 'auth://resources/mfa-capabilities';

    /**
     * Return the resource contents.
     */
    public function read(): string
    {
        return json_encode([
            'available_methods' => [
                'totp' => [
                    'name' => 'Time-based One-Time Password',
                    'security_level' => 'medium',
                    'user_friction' => 'low',
                    'setup_required' => true,
                    'best_for' => 'Moderate risk scenarios, regular users',
                ],
                'voice' => [
                    'name' => 'Voice Biometric Recognition',
                    'security_level' => 'high',
                    'user_friction' => 'medium',
                    'setup_required' => true,
                    'best_for' => 'High risk scenarios, sensitive operations',
                ],
            ],
            'combination_rules' => [
                'low_risk' => [],
                'medium_risk' => ['totp'],
                'high_risk' => ['totp', 'voice'],
            ],
        ], JSON_PRETTY_PRINT);
    }
}
