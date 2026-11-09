<?php

use App\Enums\UserRole;
use App\Models\User;

test('users table escapes the name, email and avatar path in the user info column', function () {
    $superadmin = User::factory()->create([
        'role' => UserRole::SuperAdmin->value,
    ]);

    $victim = User::factory()->create([
        'name' => '<img src=x onerror=alert(1)>',
        'email' => '<b>dummy</b>@example.com',
        'profile_photo_path' => '/storage/profile_photos/1_a.gif"onerror="alert(2)',
    ]);

    $response = $this->actingAs($superadmin)
        ->get('/dashboard/users?draw=1&start=0&length=50', ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk();

    $userInfo = collect($response->json('data'))->firstWhere('id', $victim->id)['user_info'];

    expect($userInfo)
        ->toContain('&lt;img src=x onerror=alert(1)&gt;')
        ->toContain('&lt;b&gt;dummy&lt;/b&gt;@example.com')
        ->not->toContain('<img src=x onerror=alert(1)>')
        ->not->toContain('<b>dummy</b>')
        ->not->toContain('"onerror="alert(2)');
});
