<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;

Route::get('', [TenantController::class, 'index']);
