<?php

namespace App\Http\Responses\Post;

use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

/**
 * Class ShowPostResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de post no contexto
 * de visualização.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 * - Uso consistente de Resources
 *
 * @package App\Http\Responses\Post
 */
class ShowPostResponse
{
    /**
     * Cria uma resposta de visualização de um post;
     * 
     * @param Post $post Post a ser visualizado
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function fromModel(Post $post): JsonResponse
    {
        return response()->json([
            'data' => new PostResource($post),
        ]);
    }
}