<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
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
            'totp_activated_at' => date('Y-m-d H:i:s')
        ]);

        UserVoice::create([
            'user_id' => $admin->id,
            'embeddings' => '5cF7PzMAWsEn4JvAsBpqQRdZicCl/ag/0lJxvxpFx0GF/H5BDs+pwdyatkG+aixB/tY2Qo708cHOIw7B6af9QMWMZsGJQNpAv5NoQSBLD0Irt1fBDcikwJB0zcAGI0RBeHEGQpury0F2e8fAhWRlQVFmLML/La3AES4DQvX4eb+cwzxApxiDwXUbCcJSYwtCYCsDwehy8cHqyQHBSRUAQbpz0b8nznJB7B5mQqqZ1sGVIEBBC0K3QT1losH1U0ZBGCUfwdtS/MHwwSZAva3wQTkzpMGPSR9BdUPlQQa6GEHHRlpBwJqJwag7wcHp76LAdlNuP1mjpMHSu7VArBdEwRkPbUGT+E5BNk+4wEt5IUIVBd9AV7HXwcqmncH2cSHBIFDQQQXspEHxR4HA7agTwm5sQ0Dqm2fBNc3dQWHFYcEPzcHBCq1wwStEWcHkKq7BGiH/v4jQh0GiRPHAvW4+QY9oBsKq4xNCVt4bwshGo79bAFdCAuFQQa95O0Ea1q1BkVDHPy/khEHXxgzC7KOiwQsmn0BRzRrC6gZ6QTQRfkBiapq/CeEdwg13FkDgHPXA8R+BwZzQxEG0epNBP0oHQTqouUCceG7BoO27QUBME8K9wOk+TWqqQKFD5EBQMzhC/YnDQR8rGcKOAiW/x97iwXogbcHN3IzBcX/cQTuAQMIRLoZB0F+mwWt1Y0LzqGDBCF1GwLlIQ8HM4jFBx1a6QWtupkFvici+8URuQdeeL8Bk31fAayQrws3p7cHM4D3B9e7oQeCjHkJTZsxALD+LQdPGtMEyMJzB3ZUCQuHkgUHZ358/WFy7QX8l4UDOXgfC7/o1wRyKosES6c3BKUAXQmAei0HchN/Bgm68wKVOisF2pjDBeJqawSeQx8GNEuPBg/8TQgZ3tEFCQgpBpALdv+pl10FLtE/BrO23wdBs8sA9p0dBSFu4QBhC2MDoC4BBdKQDwqgD40HKQoJAY7RuQWrQDMEV9LtBOdOqwb3LEkEYFn1A1TxWQBCZNEJt2jZA',
        ]);

        $admin->assignRole(RoleEnum::ADMIN);

    }
}
