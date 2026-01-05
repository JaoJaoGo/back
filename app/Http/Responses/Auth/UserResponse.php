<?php

namespace App\Http\Responses\Auth;

use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Class UserResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de usuário no contexto
 * de autenticação/cadastro.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 * - Uso consistente de Resources
 *
 * @package App\Http\Responses\Auth
 */
class UserResponse
{
    /**
     * Retorna a resposta HTTP para criação bem-sucedida de usuário.
     *
     * Encapsula:
     * - Mensagem de sucesso
     * - Transformação do usuário via {@see UserResource}
     * - Status HTTP 201 (Created)
     *
     * @param User $user Instância do usuário criado
     *
     * @return JsonResponse Resposta JSON de criação do usuário
     */
    public static function created(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'data' => new UserResource($user),
        ], 201);
    }
}