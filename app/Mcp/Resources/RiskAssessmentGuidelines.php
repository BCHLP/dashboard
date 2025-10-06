<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Server\Resource;

class RiskAssessmentGuidelines extends Resource
{
    protected string $description = 'Guidelines for assessing authentication risk levels and determining appropriate MFA requirements';

    protected string $uri = 'auth://resources/risk-guidelines';

    /**
     * Return the resource contents.
     */
    public function read(): string
    {
        return "# Authentication Risk Assessment Guidelines

## Risk Levels

### LOW RISK
Require: No additional MFA
Indicators:
- Login from previously seen device (fingerprint hash matches)
- Location matches user's common locations (within same city/country)
- Time falls within user's typical login hours (±2 hours)
- No recent failed login attempts
- IP address from known ISP/range

### MEDIUM RISK
Require: TOTP (Time-based One-Time Password)
Indicators:
- New device but from known location
- Known device but unusual time (outside normal hours but not extreme)
- Different city within same country
- 1-3 failed attempts in last 24 hours
- Minor timezone offset discrepancy

### HIGH RISK
Require: TOTP + Voice Recognition (both)
Indicators:
- Login from new country
- Login at highly unusual hours (3am-6am when user typically logs in 9am-5pm)
- New device + new location combination
- 4+ failed attempts in last 24 hours
- Timezone offset doesn't match claimed location (VPN/proxy indicator)
- Device fingerprint shows inconsistencies (screen size changed dramatically)
- Login immediately after password change

## Special Considerations

- First-time logins: Always require TOTP minimum
- After password reset: Always require TOTP + Voice
- Known VPN indicators: Escalate one level
- Multiple simultaneous sessions from different locations: HIGH RISK";
    }
}
