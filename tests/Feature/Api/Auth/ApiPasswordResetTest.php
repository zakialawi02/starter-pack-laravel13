<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

describe('API Password Reset', function () {
    beforeEach(function () {
        Notification::fake();
    });

    describe('Password Reset Link Generation', function () {
        test('can request password reset link with valid email', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
            ]);

            $response = $this->postJson('/api/auth/forgot-password', [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(200)
                ->assertExactJson([
                    'success' => true,
                    'message' => 'If an account with that email exists, a password reset link has been sent.',
                ]);

            // Verify reset token was created in database
            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => 'test@example.com',
            ]);

            // Verify notification was sent
            Notification::assertSentTo($user, ResetPassword::class);
        });

        test('password reset link request for unknown email returns the same generic response', function () {
            $response = $this->postJson('/api/auth/forgot-password', [
                'email' => 'nonexistent@example.com',
            ]);

            $response->assertStatus(200)
                ->assertExactJson([
                    'success' => true,
                    'message' => 'If an account with that email exists, a password reset link has been sent.',
                ]);

            // Verify no token was created
            $this->assertDatabaseMissing('password_reset_tokens', [
                'email' => 'nonexistent@example.com',
            ]);

            Notification::assertNothingSent();
        });

        test('password reset link uses the configured app url instead of the request host', function () {
            config(['app.url' => 'https://app.example.test']);

            $user = User::factory()->create([
                'email' => 'victim-dummy@example.com',
            ]);

            $this->postJson('http://attacker.test/api/auth/forgot-password', [
                'email' => 'victim-dummy@example.com',
            ])->assertStatus(200);

            Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
                return str_starts_with($notification->toMail($user)->actionUrl, 'https://app.example.test/reset-password/');
            });
        });

        test('password reset link generation requires email field', function () {
            $response = $this->postJson('/api/auth/forgot-password', []);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['email']);
        });

        test('password reset link generation requires valid email format', function () {
            $response = $this->postJson('/api/auth/forgot-password', [
                'email' => 'invalid-email',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['email']);
        });

        test('password reset link updates existing token for same user', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
            ]);

            // Create initial token
            DB::table('password_reset_tokens')->insert([
                'email' => 'test@example.com',
                'token' => Hash::make('old-token'),
                'created_at' => now()->subHour(),
            ]);

            $response = $this->postJson('/api/auth/forgot-password', [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(200);

            // Should still have only one record for this email
            $tokens = DB::table('password_reset_tokens')
                ->where('email', 'test@example.com')
                ->get();

            expect($tokens->count())->toBe(1);

            // Token should be updated (created_at should be more recent)
            expect($tokens->first()->created_at)->toBeGreaterThan(now()->subMinute());
        });

        test('password reset link response does not disclose the emailed reset token', function () {
            $user = User::factory()->create([
                'email' => 'victim-dummy@example.com',
            ]);

            $response = $this->postJson('/api/auth/forgot-password', [
                'email' => 'victim-dummy@example.com',
            ]);

            $response->assertStatus(200)
                ->assertJsonMissingPath('url_reset');

            Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($response) {
                return ! str_contains($response->getContent(), $notification->token);
            });
        });
    });

    describe('Password Reset Functionality', function () {
        test('can reset password with valid token', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('oldpassword'),
            ]);

            // Create a password reset token
            $token = 'valid-reset-token';
            DB::table('password_reset_tokens')->insert([
                'email' => 'test@example.com',
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            $response = $this->postJson('/api/auth/reset-password', [
                'token' => $token,
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                ]);

            // Verify password was changed
            $user->refresh();
            expect(Hash::check('newpassword123', $user->password))->toBeTrue();
            expect(Hash::check('oldpassword', $user->password))->toBeFalse();

            // Verify remember token was regenerated
            expect($user->remember_token)->not()->toBeNull();
        });

        test('password reset fails with invalid token', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
            ]);

            $response = $this->postJson('/api/auth/reset-password', [
                'token' => 'invalid-token',
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                ]);
        });

        test('password reset requires token field', function () {
            $response = $this->postJson('/api/auth/reset-password', [
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['token']);
        });

        test('password reset requires email field', function () {
            $response = $this->postJson('/api/auth/reset-password', [
                'token' => 'some-token',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['email']);
        });

        test('password reset requires password field', function () {
            $response = $this->postJson('/api/auth/reset-password', [
                'token' => 'some-token',
                'email' => 'test@example.com',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['password']);
        });

        test('password reset requires valid email format', function () {
            $response = $this->postJson('/api/auth/reset-password', [
                'token' => 'some-token',
                'email' => 'invalid-email',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['email']);
        });

        test('password reset requires password confirmation', function () {
            $response = $this->postJson('/api/auth/reset-password', [
                'token' => 'some-token',
                'email' => 'test@example.com',
                'password' => 'newpassword123',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['password']);
        });

        test('password reset requires matching password confirmation', function () {
            $response = $this->postJson('/api/auth/reset-password', [
                'token' => 'some-token',
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['password']);
        });

        test('password reset fails with expired token', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
            ]);

            // Create an expired token (older than 60 minutes)
            $token = 'expired-token';
            DB::table('password_reset_tokens')->insert([
                'email' => 'test@example.com',
                'token' => Hash::make($token),
                'created_at' => now()->subHours(2),
            ]);

            $response = $this->postJson('/api/auth/reset-password', [
                'token' => $token,
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                ]);
        });

        test('password reset fails with non-existent user', function () {
            $response = $this->postJson('/api/auth/reset-password', [
                'token' => 'some-token',
                'email' => 'nonexistent@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                ]);
        });

        test('password reset enforces password rules', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
            ]);

            $token = 'valid-token';
            DB::table('password_reset_tokens')->insert([
                'email' => 'test@example.com',
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            // Test with weak password
            $response = $this->postJson('/api/auth/reset-password', [
                'token' => $token,
                'email' => 'test@example.com',
                'password' => '123',
                'password_confirmation' => '123',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonValidationErrors(['password']);
        });

        test('successful password reset cleans up token', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
            ]);

            $token = 'valid-token';
            DB::table('password_reset_tokens')->insert([
                'email' => 'test@example.com',
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            $response = $this->postJson('/api/auth/reset-password', [
                'token' => $token,
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(201);

            // Token should be removed after successful reset
            $this->assertDatabaseMissing('password_reset_tokens', [
                'email' => 'test@example.com',
            ]);
        });
    });

    test('password reset endpoints handle rate limiting', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // The forgot-password endpoint should have throttle:6,1 middleware
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/auth/forgot-password', [
                'email' => 'test@example.com',
            ]);

            if ($i < 5) {
                $response->assertStatus(200);
            }
        }

        // 7th request should be rate limited
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(429); // Too Many Requests
    });

    test('password reset response formats are consistent', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Test forgot password response format
        $forgotResponse = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $forgotData = $forgotResponse->json();
        expect($forgotData)->toHaveKeys(['success', 'message']);
        expect($forgotData['success'])->toBeBool();
        expect($forgotData['message'])->toBeString();

        // Test reset password validation error format
        $resetResponse = $this->postJson('/api/auth/reset-password', []);

        $resetData = $resetResponse->json();
        expect($resetData)->toHaveKeys(['success', 'errors']);
        expect($resetData['success'])->toBeFalse();
        expect($resetData['errors'])->toBeArray();
    });
});
