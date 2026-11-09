<?php

use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    config(['services.github' => [
        'client_id' => 'dummy',
        'client_secret' => 'dummy',
        'redirect' => 'http://localhost/auth/github/callback',
    ]]);

    $socialUser = (new SocialiteUser)->map([
        'id' => '1001',
        'nickname' => 'dummy',
        'name' => 'Dummy',
        'email' => 'dummy@example.test',
    ])->setToken('dummy-token');

    Socialite::shouldReceive('driver->user')->andReturn($socialUser);
});

test('oauth login ignores a stored redirect that leaves the site', function (string $target) {
    $response = $this->withSession(['oauth_redirect' => $target])
        ->get('/auth/github/callback');

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
})->with([
    'absolute url' => 'https://attacker.example/landing',
    'protocol-relative url' => '//attacker.example/landing',
]);

test('oauth login follows a stored redirect that stays on the site', function () {
    $response = $this->withSession(['oauth_redirect' => '/dashboard/profile'])
        ->get('/auth/github/callback');

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard/profile');
});
