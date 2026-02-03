<?php

use App\Http\Controllers\Auth\RegisterBarbershopController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Support\AppRouter;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register'])->middleware('identify.barbershop');
Route::post('/register-barbershop', [RegisterBarbershopController::class, 'store']);

Route::middleware(['auth:sanctum', 'identify.barbershop'])->group(function () {
    // protegidas
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::get('auth/logout', [AuthController::class, 'logout']);
    AppRouter::load(base_path('routes/private'));
});
