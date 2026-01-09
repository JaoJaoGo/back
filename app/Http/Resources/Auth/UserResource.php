<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 *
 * Resource responsável pela transformação da entidade
 * {@see User} em uma estrutura JSON
 * padronizada para respostas da API.
 *
 * Garante que apenas os campos necessários sejam
 * expostos ao cliente, evitando vazamento de
 * dados sensíveis e mantendo consistência
 * entre diferentes endpoints.
 *
 * @package App\Http\Resources\Auth
 */
class UserResource extends JsonResource
{
    /**
     * Transforma o recurso em um array serializável.
     *
     * Campos retornados:
     * - id: identificador único do usuário
     * - name: nome completo
     * - age: idade
     * - birth_date: data de nascimento
     * - phone: telefone
     * - email: e-mail
     * - created_at: data de criação do registro
     *
     * @param Request $request Requisição HTTP atual
     *
     * @return array<string, mixed> Representação do usuário para a API
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'birth_date' => $this->birth_date,
            'phone' => $this->phone,
            'email' => $this->email,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}