<?php

namespace App\Http\Resources\Tag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TagListResource
 *
 * Resource responsável pela transformação
 * da entidade Post para o contexto de LISTAGEM.
 *
 * Retorna apenas os dados necessários
 * para exibição em listas, evitando
 * payloads excessivos.
 *
 * @package App\Http\Resources\Tag
 */
class TagListResource extends JsonResource
{
    /**
     * Transforma o resource em array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
