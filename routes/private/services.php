<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('', [ServiceController::class, 'index']);
Route::post('', [ServiceController::class, 'store']);
Route::get('{service}', [ServiceController::class, 'show']);
Route::put('{service}', [ServiceController::class, 'update']);
Route::delete('{service}', [ServiceController::class, 'destroy']);
