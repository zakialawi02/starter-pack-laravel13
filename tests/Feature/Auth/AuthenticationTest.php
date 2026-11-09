<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'id_user' => $user->email, // or use $user->username
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'id_user' => $user->email, // or use $user->username
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('username login is locked out after five failures even when the email field changes', function () {
    User::factory()->create(['username' => 'victimuser']);

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', [
            'id_user' => 'victimuser',
            'password' => "wrong-{$attempt}",
            'email' => "rotated-{$attempt}@dummy.test",
        ]);
    }

    $this->post('/login', [
        'id_user' => 'victimuser',
        'password' => 'password',
        'email' => 'rotated-6@dummy.test',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('login ignores a redirect parameter that leaves the site', function (string $target) {
    $user = User::factory()->create();

    $response = $this->withHeader('Referer', url('/login').'?redirect='.urlencode($target))
        ->post('/login', [
            'id_user' => $user->email,
            'password' => 'password',
        ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
})->with([
    'absolute url' => 'https://attacker.example/landing',
    'protocol-relative url' => '//attacker.example/landing',
    'backslash-prefixed path' => '/\\attacker.example/landing',
]);

test('login follows a redirect parameter that stays on the site', function (string $target) {
    $user = User::factory()->create();

    $response = $this->withHeader('Referer', url('/login').'?redirect='.urlencode($target))
        ->post('/login', [
            'id_user' => $user->email,
            'password' => 'password',
        ]);

    $response->assertRedirect(url('/dashboard/profile'));
})->with([
    'absolute same-origin url' => fn () => url('/dashboard/profile'),
    'relative path' => '/dashboard/profile',
]);

test('login screen does not turn an external referer into a redirect parameter', function () {
    $this->withHeader('Referer', 'https://attacker.example/')
        ->get('/login')
        ->assertOk();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
