<?php

namespace App\Http\Services\Auth;

use App\Http\Repositories\Auth\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use RuntimeException;

/**
 * Class UserService
 *
 * Serviço responsável pelas regras de negócio
 * relacionadas à criação de usuários.
 *
 * Centraliza validações e políticas que não
 * pertencem ao controller ou ao repositório,
 * garantindo consistência e fácil manutenção.
 *
 * @package App\Http\Services\Auth
 */
class UserService
{
    /**
     * Cria uma nova instância do serviço.
     *
     * O {@see UserRepository} é injetado para
     * desacoplar o acesso a dados da regra
     * de negócio e facilitar testes.
     *
     * @param UserRepository $repository Repositório de usuários
     */
    public function __construct(
        protected UserRepository $repository
    ) {}

    /**
     * Cria um novo usuário no sistema.
     *
     * Regras de negócio aplicadas:
     * - Limite máximo de usuários permitidos
     * - Criptografia da senha antes da persistência
     *
     * Os dados devem estar previamente validados
     * pela camada de Request ou por outro mecanismo
     * de validação.
     *
     * @param array<string, mixed> $data Dados do usuário
     *
     * @return User Usuário criado
     *
     * @throws RuntimeException Quando o limite máximo de usuários é atingido
     */
    public function create(array $data): User
    {
        if ($this->repository->count() >= 2) {
            throw new RuntimeException("Número máximo de usuários atingidos.");
        }

        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }
}