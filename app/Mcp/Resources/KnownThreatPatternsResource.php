<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Server\Resource;

class KnownThreatPatternsResource extends Resource
{
    protected string $description = 'Database of known attack patterns and threat indicators';

    protected string $uri = 'auth://resources/threat-patterns';

    /**
     * Return the resource contents.
     */
    public function read(): string
    {
        return "# Known Authentication Threat Patterns

## Credential Stuffing
- Multiple failed attempts from same IP
- Attempts against multiple user accounts
- User-Agent rotation
- Short time between attempts (< 5 seconds)

## Account Takeover Indicators
- Successful login from new location immediately after failed attempts
- Login followed by rapid profile changes
- Access from known proxy/VPN services after history of direct connections

## Location Spoofing
- Timezone offset doesn't match IP geolocation
- Two logins from geographically distant locations within impossible timeframe
- Browser timezone differs from claimed location

## Device Fingerprint Manipulation
- Canvas fingerprint changes but other attributes identical
- Missing or generic WebGL renderer
- Suspicious font lists (too few or too many)
- Disabled JavaScript features (localStorage, cookies) when previously enabled";
    }
}
