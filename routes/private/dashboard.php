<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('today_appointments', [DashboardController::class, 'todayAppointments']);
