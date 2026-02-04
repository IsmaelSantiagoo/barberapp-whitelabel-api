<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarbershopController;

Route::get('', [BarbershopController::class, 'index']);
Route::get('{barbershop_slug}', [BarbershopController::class, 'find']);
Route::put('{barbershop}', [BarbershopController::class, 'update']);

