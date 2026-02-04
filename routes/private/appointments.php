<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('', [AppointmentController::class, 'index']);
Route::post('', [AppointmentController::class, 'store']);

Route::get('client', [AppointmentController::class, 'getByClient']);

Route::get('{appointment}', [AppointmentController::class, 'show']);
Route::put('{appointment}', [AppointmentController::class, 'update']);

Route::post('/cancel/{appointment}', [AppointmentController::class, 'cancel']);
Route::post('/complete/{appointment}', [AppointmentController::class, 'complete']);
