<?php

namespace App\Http\Responses\Post;

use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

/**
 * Class StorePostResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de post no contexto
 * de criação.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 * - Uso consistente de Resources
 *
 * @package App\Http\Responses\Post
 */
class StorePostResponse
{
    /**
     * Cria uma resposta HTTP padronizada
     * 
     * @param Post $post Post criado
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function fromModel(Post $post): JsonResponse
    {
        return response()->json([
            'messsage' => 'Post criado com sucesso',
            'data' => PostResource::make($post),
        ], 201);
    }
}