<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('users', UserController::class)
            ->middleware(['verified', 'role:superadmin']);
        Route::get('/user', [UserController::class, 'me'])->name('user.me');
        Route::patch('/user', [UserController::class, 'updateMyProfile'])->name('user.me');
        Route::patch('/user/photo', [UserController::class, 'updateMyPhotoProfile'])->name('user.photo');
        Route::delete('/user/destroy/me', [UserController::class, 'destroyMyAccount'])->name('user.destroy.me');
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        //
    });
})->middleware('api');

// Include auth routes
require __DIR__.'/api/auth.php';
