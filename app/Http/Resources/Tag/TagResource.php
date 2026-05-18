<?php

namespace App\Http\Resources\Tag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TagResource
 *
 * Resource responsável pela transformação
 * da entidade Tag para o contexto de LISTAGEM.
 *
 * Retorna apenas os dados necessários
 * para exibição em listas, evitando
 * payloads excessivos.
 *
 * @package App\Http\Resources\Tag
 */
class TagResource extends JsonResource
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
