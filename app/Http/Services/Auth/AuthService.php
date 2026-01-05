<?php

namespace App\Http\Services\Auth;

use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

/**
 * Class AuthService
 *
 * Serviço responsável pela lógica de autenticação
 * de usuários no sistema.
 *
 * Centraliza o processo de login, validação de credenciais
 * e geração de tokens de acesso, mantendo os controllers
 * desacoplados das regras de negócio.
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
     * - Gera um token de acesso (ex: Laravel Sanctum)
     *
     * Em caso de falha, uma {@see AuthenticationException}
     * é lançada, devendo ser tratada pela camada HTTP.
     *
     * @param array<string, string> $credentials Credenciais do usuário (email e password)
     *
     * @return array{
     *     user: UserResource,
     *     token: string,
     *     token_type: string
     * }
     * Estrutura de dados retornada após autenticação bem-sucedida
     *
     * @throws AuthenticationException Quando as credenciais forem inválidas
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Email ou senha inválidos');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}