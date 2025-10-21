<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Actions\CreateServer;
use App\Enums\RoleEnum;
use App\Events\DatapointCreatedEvent;
use App\Models\User;
use App\Models\UserVoice;

pest()->extend(Tests\TestCase::class)->beforeEach(function () {
    Event::fake([DatapointCreatedEvent::class]);
    $this->seed(\Database\Seeders\TestingSeeder::class);
})
    // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createUser(RoleEnum $role = RoleEnum::NONE): User
{
    $user = User::factory(['totp_secret' => '123', 'totp_activated_at' => now()])->create();
    if ($role !== RoleEnum::NONE) {
        $user->assignRole($role);
    }
    UserVoice::create(['user_id' => $user->id, 'embeddings' => 'embedded']);

    return $user;
}

function createServer(string $name = 'server')
{
    $createServer = app(CreateServer::class);
    $result = $createServer($name);

    return $result['server'];
}

function disableMfaAuthentication(): void
{
    session(['voice' => time()]);
}
