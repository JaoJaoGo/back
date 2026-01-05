<?php

namespace App\Http\Repositories\Auth;

use App\Models\User;

/**
 * Class UserRepository
 * 
 * Repositório responsável pelo acesso e persistência
 * de dados relacionados à entidade {@see User}.
 * 
 * Esta camada abstrai o uso direto do Eloquent,
 * permitindo desacoplamento da regra de negócio,
 * maior testabilidade e facilidade de manutenção.
 * 
 * @package App\Http\Repositories\Auth
 */
class UserRepository
{
    /**
     * Cria um novo usuário no banco de dados.
     * 
     * Os dados devem estar previamente validados
     * antes de serem enviados a este método.
     * 
     * @param array<string, mixed> $data Dados do usuário
     * 
     * @return User Instância do usuário criado
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Retorna a quantidade total de usuários cadastrados.
     * 
     * Útil para métricas, dashboards ou regras de negócio
     * que dependem do volume de usuários.
     * 
     * @return int Número total de usuários
     */
    public function count(): int
    {
        return User::count();
    }
}