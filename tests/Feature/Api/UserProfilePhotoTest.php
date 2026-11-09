<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

test('api profile photo is stored with the extension of its content, not the client filename', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->withHeader('Accept', 'application/json')
        ->patch('/api/v1/user/photo', [
            'photo_profile' => gifPolyglotUpload('x.html'),
        ])
        ->assertCreated();

    $photoPath = $user->fresh()->profile_photo_path;

    expect($photoPath)->toEndWith('.gif');
    Storage::disk('public')->assertExists(Str::after($photoPath, '/storage/'));
});
