<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('', [AppointmentController::class, 'index']);
Route::post('', [AppointmentController::class, 'store']);
Route::get('{appointment}', [AppointmentController::class, 'show']);
Route::put('{appointment}', [AppointmentController::class, 'update']);

Route::post('{appointment}/cancel', [AppointmentController::class, 'cancel']);
Route::post('{appointment}/complete', [AppointmentController::class, 'complete']);
