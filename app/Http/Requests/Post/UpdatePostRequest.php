<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdatePostRequest
 *
 * Request responsável pela validação dos dados
 * necessários para atualização de um post.
 *
 * Centraliza as regras de validação do cadastro,
 * garantindo consistência entre diferentes pontos
 * de entrada (API, CLI, etc.).
 *
 * @package App\Http\Requests\Post
 */
class UpdatePostRequest extends FormRequest
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
     * - title: título do post (opcional)
     * - subtitle: sub-título do post (opcional)
     * - content: conteúdo do post (opcional)
     * - image: imagem do post (opcional)
     * - author: autor do post (opcional)
     * - tags: tags do post (obrigatório, pelo menos 1)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'author' => ['sometimes', 'string', 'max:255'],

            // Upload
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],

            // Tags
            'tags' => ['sometimes', 'array', 'min:1'],
            'tags.*' => ['string', 'max:50'],
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

        if (isset($data['tags'])) {
            $data['tags'] = collect($data['tags'])
                ->map(fn ($tag) => trim(mb_strtolower($tag)))
                ->unique()
                ->values()
                ->toArray();
        }

        return $data;
    }
}