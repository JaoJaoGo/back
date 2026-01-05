<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

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
});