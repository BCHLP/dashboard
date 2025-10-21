<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserVoice;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'David Belle',
            'email' => 'djbelle@our.ecu.edu.au',
            'password' => '909aJq1NS|1H',
            'totp_secret' => 'MN6O75VRYRZ4OBV7',
            'totp_activated_at' => date('Y-m-d H:i:s'),
        ]);

        UserVoice::create([
            'user_id' => $admin->id,
            'embeddings' => '5cF7PzMAWsEn4JvAsBpqQRdZicCl/ag/0lJxvxpFx0GF/H5BDs+pwdyatkG+aixB/tY2Qo708cHOIw7B6af9QMWMZsGJQNpAv5NoQSBLD0Irt1fBDcikwJB0zcAGI0RBeHEGQpury0F2e8fAhWRlQVFmLML/La3AES4DQvX4eb+cwzxApxiDwXUbCcJSYwtCYCsDwehy8cHqyQHBSRUAQbpz0b8nznJB7B5mQqqZ1sGVIEBBC0K3QT1losH1U0ZBGCUfwdtS/MHwwSZAva3wQTkzpMGPSR9BdUPlQQa6GEHHRlpBwJqJwag7wcHp76LAdlNuP1mjpMHSu7VArBdEwRkPbUGT+E5BNk+4wEt5IUIVBd9AV7HXwcqmncH2cSHBIFDQQQXspEHxR4HA7agTwm5sQ0Dqm2fBNc3dQWHFYcEPzcHBCq1wwStEWcHkKq7BGiH/v4jQh0GiRPHAvW4+QY9oBsKq4xNCVt4bwshGo79bAFdCAuFQQa95O0Ea1q1BkVDHPy/khEHXxgzC7KOiwQsmn0BRzRrC6gZ6QTQRfkBiapq/CeEdwg13FkDgHPXA8R+BwZzQxEG0epNBP0oHQTqouUCceG7BoO27QUBME8K9wOk+TWqqQKFD5EBQMzhC/YnDQR8rGcKOAiW/x97iwXogbcHN3IzBcX/cQTuAQMIRLoZB0F+mwWt1Y0LzqGDBCF1GwLlIQ8HM4jFBx1a6QWtupkFvici+8URuQdeeL8Bk31fAayQrws3p7cHM4D3B9e7oQeCjHkJTZsxALD+LQdPGtMEyMJzB3ZUCQuHkgUHZ358/WFy7QX8l4UDOXgfC7/o1wRyKosES6c3BKUAXQmAei0HchN/Bgm68wKVOisF2pjDBeJqawSeQx8GNEuPBg/8TQgZ3tEFCQgpBpALdv+pl10FLtE/BrO23wdBs8sA9p0dBSFu4QBhC2MDoC4BBdKQDwqgD40HKQoJAY7RuQWrQDMEV9LtBOdOqwb3LEkEYFn1A1TxWQBCZNEJt2jZA',
        ]);

        $admin->assignRole(RoleEnum::ADMIN);

        $rohan = User::create([
            'name' => 'Rohan',
            'email' => 'RKAKARLA@our.ecu.edu.au',
            'password' => '909aJq1NS|1H',
            'totp_secret' => 'J44FCA3H2M7X4OH2',
            'totp_activated_at' => '2025-10-09 06:11:42',
        ]);

        UserVoice::create([
            'user_id' => $rohan->id,
            'embeddings' => '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',
        ]);

        $admin->assignRole(RoleEnum::ADMIN);

    }
}
