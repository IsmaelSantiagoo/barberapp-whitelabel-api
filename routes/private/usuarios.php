<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuariosController;

Route::patch('alterar-senha/{id}', [UsuariosController::class, 'alterarSenha']); // Rota para alterar a senha do usuário
Route::post('alterar-foto/{id}', [UsuariosController::class, 'alterarImagem']); // Rota para alterar a foto do usuário
Route::delete('remover-foto/{id}', [UsuariosController::class, 'removerImagem']); // Rota para remover a foto do usuário

Route::get('/menus-favoritos', [UsuariosController::class, 'getMenusFavoritos']);
Route::post('/favoritar-menu/{menu}', [UsuariosController::class, 'favoritarMenu']);
