<?php

use App\Http\Controllers\NotificacoesController;
use Illuminate\Support\Facades\Route;

Route::get('', [NotificacoesController::class, 'getAll']);
Route::post('', [NotificacoesController::class, 'dispararNotificacao']);
Route::post('lida', [NotificacoesController::class, 'marcarComoLida']);
