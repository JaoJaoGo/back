<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PostResource
 *
 * Resource responsável pela transformação da entidade
 * {@see Post} em uma estrutura JSON
 * padronizada para respostas da API.
 *
 * Garante que apenas os campos necessários sejam
 * expostos ao cliente, evitando vazamento de
 * dados sensíveis e mantendo consistência
 * entre diferentes endpoints.
 *
 * @package App\Http\Resources\Post
 */
class PostResource extends JsonResource
{
    /**
     * Transforma o resource em um array.
     * 
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'content' => $this->content,
            'image' => $this->image,
            'author' => $this->author,

            'tags' => $this->whenLoaded('tags', fn () =>
                $this->tags->pluck('name')
            ),

            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}