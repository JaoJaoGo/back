<?php

namespace App\Http\Responses\Tag;

use App\Http\Resources\Tag\TagResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Class ListTagsResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de tag no contexto
 * de listagem.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 * - Uso consistente de Resources
 *
 * @package App\Http\Responses\Tag
 */
class ListTagsResponse
{
    /**
     * Cria uma resposta de listagem de tags;
     * 
     * @param LengthAwarePaginator $paginator Paginador de tags
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'data' => TagResource::collection($paginator),
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