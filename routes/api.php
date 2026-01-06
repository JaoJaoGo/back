<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Post\PostController;

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
|
| Este arquivo define os endpoints responsáveis pelo fluxo
| de autenticação da API.
|
| As rotas públicas permitem o login do usuário.
| As rotas protegidas exigem autenticação via Sanctum.
|
*/

/**
 * Realiza o login do usuário.
 *
 * Endpoint público responsável por autenticar o usuário
 * com e-mail e senha.
 *
 * Retorna:
 * - Dados do usuário autenticado
 * - Token de acesso (Bearer)
 */
Route::post('login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Rotas Protegidas (auth:sanctum)
|--------------------------------------------------------------------------
|
| As rotas abaixo exigem que o usuário esteja autenticado
| via Laravel Sanctum.
|
*/
Route::middleware('auth:sanctum')->group(function () {
     /**
     * Retorna os dados do usuário autenticado.
     *
     * Utiliza o token presente no header Authorization
     * para identificar o usuário logado.
     */
    Route::get('/me', [AuthController::class, 'me']);

    /**
     * Realiza o logout do usuário autenticado.
     *
     * Invalida o token de acesso atual,
     * encerrando a sessão da API.
     */
    Route::post('/logout', [AuthController::class, 'logout']);

    /**
     * Rotas de Post
     * 
     * Rotas protegidas que permitem o gerenciamento de posts
     * pelo usuário autenticado.
     */
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'show'])->whereNumber('id');
        Route::post('/', [PostController::class, 'store']);
        Route::put('/{id}', [PostController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [PostController::class, 'destroy'])->whereNumber('id');
    });
});