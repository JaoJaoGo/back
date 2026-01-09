<?php

namespace App\Http\Services\Auth;

use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthService
 *
 * Serviço responsável pela lógica de autenticação
 * de usuários no sistema.
 *
 * Centraliza o processo de login, validação de credenciais
 * e retorno de dados do usuário autenticado,
 * mantendo os controllers desacoplados das regras de negócio.
 *
 * @package App\Http\Services\Auth
 */
class AuthService
{
    /**
     * Realiza a autenticação do usuário.
     *
     * Fluxo de autenticação:
     * - Busca o usuário pelo e-mail informado
     * - Valida a senha utilizando hash seguro
     *
     * Em caso de falha, uma {@see AuthenticationException}
     * é lançada, devendo ser tratada pela camada HTTP.
     *
     * @param array<string, string> $credentials Credenciais do usuário (email e password)
     *
     * @return array{
     *     user: UserResource,
     * }
     * Estrutura de dados retornada após autenticação bem-sucedida
     *
     * @throws AuthenticationException Quando as credenciais forem inválidas
     */
    public function login(array $credentials): array
    {
        if (!Auth::guard('web')->attempt($credentials)) {
            throw new AuthenticationException('Email ou senha inválidos');
        }

        $user = Auth::guard('web')->user();

        return [
            'user' => new UserResource($user),
        ];
    }
}