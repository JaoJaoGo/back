<?php

namespace App\Http\Responses\Post;

use Illuminate\Http\JsonResponse;

/**
 * Class DeletePostResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de post no contexto
 * de remoção.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 *
 * @package App\Http\Responses\Post
 */
class DeletePostResponse
{
    /**
     * Cria uma resposta HTTP padronizada
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function success(): JsonResponse
    {
        return response()->json([
            'message' => 'Post removido com sucesso.',
        ], 200);
    }
}