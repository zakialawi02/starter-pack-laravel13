<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'username' => 'superadmin',
        'role' => UserRole::SuperAdmin->value,
        'email' => 'sa-dummy@gmail.com',
    ]);

    $this->regularUser = User::factory()->unverified()->create([
        'username' => 'regular_user',
        'role' => UserRole::User->value,
        'email' => 'regular-dummy@gmail.com',
    ]);
});

test('non-superadmin token cannot list users', function () {
    Sanctum::actingAs($this->regularUser);

    $this->getJson('/api/v1/users')->assertForbidden();
});

test('non-superadmin token cannot take over the superadmin account', function () {
    Sanctum::actingAs($this->regularUser);

    $this->putJson("/api/v1/users/{$this->superadmin->id}", [
        'name' => 'Super Admin',
        'username' => 'superadmin',
        'role' => UserRole::SuperAdmin->value,
        'email' => 'sa-dummy@gmail.com',
        'password' => 'Dummy-pass-1',
    ])->assertForbidden();

    expect(Hash::check('Dummy-pass-1', $this->superadmin->fresh()->password))->toBeFalse();
});

test('non-superadmin token cannot promote itself to a verified superadmin', function () {
    Sanctum::actingAs($this->regularUser);

    $this->putJson("/api/v1/users/{$this->regularUser->id}", [
        'name' => $this->regularUser->name,
        'username' => $this->regularUser->username,
        'role' => UserRole::SuperAdmin->value,
        'email' => $this->regularUser->email,
        'email_verified_at' => true,
    ])->assertForbidden();

    $regularUser = $this->regularUser->fresh();

    expect($regularUser->role)->toBe(UserRole::User)
        ->and($regularUser->email_verified_at)->toBeNull();
});

test('non-superadmin token cannot create a superadmin', function () {
    Sanctum::actingAs($this->regularUser);

    $this->postJson('/api/v1/users', [
        'name' => 'Rogue Admin',
        'username' => 'rogue_admin',
        'role' => UserRole::SuperAdmin->value,
        'email' => 'rogue-dummy@gmail.com',
        'email_verified_at' => true,
        'password' => 'Dummy-pass-1',
    ])->assertForbidden();

    $this->assertDatabaseMissing('users', ['username' => 'rogue_admin']);
});

test('non-superadmin token cannot delete another user', function () {
    $otherUser = User::factory()->create();

    Sanctum::actingAs($this->regularUser);

    $this->deleteJson("/api/v1/users/{$otherUser->id}")->assertForbidden();

    $this->assertNotSoftDeleted($otherUser);
});

test('unverified superadmin token cannot list users', function () {
    Sanctum::actingAs(User::factory()->unverified()->create([
        'role' => UserRole::SuperAdmin->value,
    ]));

    $this->getJson('/api/v1/users')->assertForbidden();
});

test('verified superadmin token can list users', function () {
    Sanctum::actingAs($this->superadmin);

    $this->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('any authenticated token can still read its own profile', function () {
    Sanctum::actingAs($this->regularUser);

    $this->getJson('/api/v1/user')
        ->assertOk()
        ->assertJsonPath('data.id', $this->regularUser->id);
});
