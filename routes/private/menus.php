<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;

Route::get('', [MenuController::class, 'read']); // Listar todos os menus
Route::get('{id}', [MenuController::class, 'show']); // Consultar menu específico
Route::post('', [MenuController::class, 'create']); // Criar novo menu
Route::patch('edit/{id}', [MenuController::class, 'update']); // Atualizar menu
Route::delete('{id}', [MenuController::class, 'delete']); // Remover menu
