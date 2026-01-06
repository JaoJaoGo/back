<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StorePostRequest
 *
 * Request responsável pela validação dos dados
 * necessários para criação de um novo post.
 *
 * Centraliza as regras de validação do cadastro,
 * garantindo consistência entre diferentes pontos
 * de entrada (API, CLI, etc.).
 *
 * @package App\Http\Requests\Post
 */
class StorePostRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado
     * a realizar esta requisição.
     *
     * O sistema de autorização será feita via middleware.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Retorna as regras de validação aplicáveis à requisição.
     *
     * Campos validados:
     * - title: título do post (obrigatório)
     * - subtitle: sub-título do post (opcional)
     * - content: conteúdo do post (obrigatório)
     * - image: imagem do post (opcional)
     * - author: autor do post (obrigatório)
     * - tags: tags do post (obrigatório, pelo menos 1)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'author' => ['required', 'string', 'max:255'],

            /**
             * Tags:
             * - Deve ser um array
             * - Pelo menos 1 tag
             * - Cada tag deve ser uma string válida
             */
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * Retorna as mensagens de validação personalizadas.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'content.required' => 'O conteúdo é obrigatório.',
            'author.required' => 'O autor é obrigatório.',
            'tags.required' => 'Informe ao menos uma tag.',
            'tags.array' => 'As tags devem ser um array.',
            'tags.min' => 'O post deve possuir pelo menos uma tag.',
        ];
    }

    /**
     * Dados já sanitizados para criação do post.
     *
     * Remove espaços extras das tags e normaliza valores.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $data = parent::validated();

        if(isset($data['tags'])) {
            $data['tags'] = collect($data['tags'])
                ->map(fn ($tag) => trim(mb_strtolower($tag)))
                ->unique()
                ->values()
                ->toArray();
        }

        return $data;
    }
}