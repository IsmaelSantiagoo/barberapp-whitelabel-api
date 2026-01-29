<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('', [NotificationController::class, 'getAll']);
Route::post('', [NotificationController::class, 'dispararNotificacao']);
Route::post('lida', [NotificationController::class, 'marcarComoLida']);
