<?php
declare(strict_types=1);

namespace App\Enums;

enum AnthropicModelEnum : string
{
    case SONNET = 'claude-sonnet-4-5-20250929';
    case HAIKU = 'claude-haiku-4-5-20251001';
}
