<?php

namespace App\Http\Responses\Tag;

use Illuminate\Http\JsonResponse;

/**
 * Class DeleteTagResponse
 *
 * Classe responsável por centralizar respostas HTTP
 * relacionadas à entidade de tag no contexto
 * de remoção.
 *
 * Atua como uma *Response Factory*, garantindo:
 * - Padronização das respostas JSON
 * - Status HTTP corretos
 *
 * @package App\Http\Responses\Tag
 */
class DeleteTagResponse
{
    /**
     * Cria uma resposta HTTP padronizada
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public static function success(): JsonResponse
    {
        return response()->json([
            'message' => 'Tag removida com sucesso.',
        ], 200);
    }
}