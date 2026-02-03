<?php

use App\Http\Controllers\BusinessHourController;
use Illuminate\Support\Facades\Route;

Route::get('', [BusinessHourController::class, 'index']);
Route::post('', [BusinessHourController::class, 'store']);
Route::put('', [BusinessHourController::class, 'upsert']);
Route::put('/{businessHour}', [BusinessHourController::class, 'update']);
