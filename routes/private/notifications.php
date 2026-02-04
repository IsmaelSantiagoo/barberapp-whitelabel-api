<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('', [NotificationController::class, 'getAll']);
Route::post('', [NotificationController::class, 'triggerNotification']);
Route::post('read', [NotificationController::class, 'markAsRead']);
