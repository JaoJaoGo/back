<?php

namespace App\Http\Responses\Tag;

use App\Http\Resources\Tag\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

/**
 * Class UpdateTagResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de tag no contexto
 * de atualização.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 * - Uso consistente de Resources
 *
 * @package App\Http\Responses\Tag
 */
class UpdateTagResponse
{
    /**
     * Cria uma resposta HTTP padronizada
     * 
     * @param Tag $tag Tag atualizada
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function fromModel(Tag $tag): JsonResponse
    {
        return response()->json([
            'message' => "Tag atualizada com sucesso.",
            'data' => new TagResource($tag),
        ]);
    }
}