<?php

use App\Http\Controllers\Auth\RegisterBarbershopController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ClientAuthController;
use App\Http\Controllers\BarbershopController;
use Illuminate\Support\Facades\Route;
use App\Support\AppRouter;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register'])->middleware('identify.barbershop');
Route::post('/register-barbershop', [RegisterBarbershopController::class, 'store']);
Route::get('/barber-shops/{barbershop_id}', [BarbershopController::class, 'find']);

// Client auth (phone + OTP)
Route::middleware(['identify.barbershop', 'throttle:5,1'])->prefix('auth/client')->group(function () {
    Route::post('/request-otp', [ClientAuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [ClientAuthController::class, 'verifyOtp']);
    Route::post('/auto-login', [ClientAuthController::class, 'autoLogin']);
});

Route::middleware(['auth:sanctum', 'identify.barbershop'])->group(function () {
    // protegidas
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::get('auth/logout', [AuthController::class, 'logout']);
    AppRouter::load(base_path('routes/private'));
});
