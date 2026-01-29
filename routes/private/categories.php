<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('', [CategoryController::class, 'index']);
Route::post('', [CategoryController::class, 'store']);
Route::get('{category}', [CategoryController::class, 'show']);
Route::put('{category}', [CategoryController::class, 'update']);
Route::delete('{category}', [CategoryController::class, 'destroy']);
