<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Support\AppRouter;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('auth/login', [AuthController::class, 'login'])->withoutMiddleware('auth:sanctum');
Route::get('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('auth/register', [AuthController::class, 'register'])->withoutMiddleware('auth:sanctum');

Route::prefix('private')->middleware(['api', 'auth:sanctum'])->group(function () {
    // protegidas
    AppRouter::load(base_path('routes/private'));
});
