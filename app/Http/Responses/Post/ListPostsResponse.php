<?php

namespace App\Http\Responses\Post;

use App\Http\Resources\Post\PostListResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Class ListPostsResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de post no contexto
 * de listagem.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 * - Uso consistente de Resources
 *
 * @package App\Http\Responses\Post
 */
class ListPostsResponse
{
    /**
     * Cria uma resposta de listagem de posts;
     * 
     * @param LengthAwarePaginator $paginator Paginador de posts
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'data' => PostListResource::collection($paginator),
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'lastPage' => $paginator->lastPage(),
                'hasMorePages' => $paginator->hasMorePages(),
            ],
        ]);
    }
}