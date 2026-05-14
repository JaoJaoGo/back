<?php

namespace App\Http\Responses\Tag;

use App\Http\Resources\Tag\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

/**
 * Class ShowTagResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de tag no contexto
 * de visualização.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 * - Uso consistente de Resources
 *
 * @package App\Http\Responses\Tag
 */
class ShowTagResponse
{
    /**
     * Cria uma resposta de visualização de um tag;
     * 
     * @param Tag $tag Tag a ser visualizado
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function fromModel(Tag $tag): JsonResponse
    {
        return response()->json([
            'data' => new TagResource($tag),
        ]);
    }
}