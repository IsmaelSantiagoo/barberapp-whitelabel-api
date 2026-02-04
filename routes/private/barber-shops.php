<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarbershopController;

Route::get('', [BarbershopController::class, 'index']);
Route::put('{barbershop}', [BarbershopController::class, 'update']);

