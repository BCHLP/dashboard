<?php

use App\Enums\RoleEnum;
use App\Services\VoiceRecognitionService;
use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('you cannot see the users page without the User Management role', function() {

    $plep = createUser();
    $this->actingAs($plep);
    disableMfaAuthentication();

    expect(VoiceRecognitionService::isVoiceAuthenticated())->toBeTrue()
        ->and($this->get(route('users.index'))->content())->toContain('This action is unauthorized')
        ->and($this->get(route('users.create'))->content())->toContain('This action is unauthorized')
        ->and($this->get(route('users.edit', $plep))->content())->toContain('This action is unauthorized')
        ->and($this->post(route('users.store'))->content())->toContain('This action is unauthorized')
        ->and($this->post(route('users.update', $plep))->content())->toContain('This action is unauthorized')
        ->and($this->post(route('users.destroy', $plep))->content())->toContain('This action is unauthorized');

})->only();

test('you can see the users page with the User Management role', function() {

    $manager = createUser(RoleEnum::USER_MANAGEMENT);
    $this->actingAs($manager);
    disableMfaAuthentication();

    expect(VoiceRecognitionService::isVoiceAuthenticated())->toBeTrue()
        ->and($this->get(route('users.index'))->content())->not->toContain('This action is unauthorized')
        ->and($this->get(route('users.create'))->content())->not->toContain('This action is unauthorized')
        ->and($this->get(route('users.edit', $manager))->content())->not->toContain('This action is unauthorized')
        ->and($this->post(route('users.store'))->content())->not->toContain('This action is unauthorized')
        ->and($this->post(route('users.update', $manager))->content())->not->toContain('This action is unauthorized')
        ->and($this->post(route('users.destroy', $manager))->content())->not->toContain('This action is unauthorized');


})->only();

test('new users can register', function () {



    $response = $this->post('/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
