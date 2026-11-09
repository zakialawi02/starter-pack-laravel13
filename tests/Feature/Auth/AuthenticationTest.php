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

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
