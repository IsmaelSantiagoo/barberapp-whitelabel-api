<?php

use App\Http\Controllers\Auth\RegisterTenantController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Support\AppRouter;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('/register-tenant', [RegisterTenantController::class, 'store']);

Route::middleware(['api', 'auth:sanctum', 'identify.tenant'])->group(function () {
    // protegidas
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::get('auth/logout', [AuthController::class, 'logout']);
    AppRouter::load(base_path('routes/private'));
});
