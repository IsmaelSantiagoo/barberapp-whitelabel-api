<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('', [UserController::class, 'index']);
Route::patch('change-password/{id}', [UserController::class, 'alterarSenha']); // Rota para alterar a senha do usuário
Route::post('change-photo/{id}', [UserController::class, 'alterarImagem']); // Rota para alterar a foto do usuário
Route::delete('remove-photo/{id}', [UserController::class, 'removerImagem']); // Rota para remover a foto do usuário

Route::get('/favorite-menus', [UserController::class, 'getFavoriteMenus']);
Route::post('/favorite-menu/{menu}', [UserController::class, 'favoriteMenu']);
