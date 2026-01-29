<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenusController;

Route::get('', [MenusController::class, 'read']); // Listar todos os menus
Route::get('{id}', [MenusController::class, 'show']); // Consultar menu específico
Route::post('', [MenusController::class, 'create']); // Criar novo menu
Route::patch('edit/{id}', [MenusController::class, 'update']); // Atualizar menu
Route::delete('{id}', [MenusController::class, 'delete']); // Remover menu
