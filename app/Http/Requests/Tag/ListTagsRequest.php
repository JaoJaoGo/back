<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/**
 * Class ListTagsRequest
 *
 * Request responsável por validar filtros,
 * paginação e ordenação da listagem de tags.
 *
 * Centraliza regras de entrada para o endpoint
 * de index/listagem.
 *
 * @package App\Http\Requests\Tag
 */
class ListTagsRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado
     * a realizar esta requisição.
     *
     * Não há restrição de autorização.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza os dados antes da validação.
     *
     * Converte camelCase → snake_case
     * e aplica aliases de campos.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $input = $this->all();

        $mapped = [
            'perPage' => 'per_page',
            'sortBy' => 'sort',
            'sortDirection' => 'direction',
        ];

        foreach ($mapped as $from => $to) {
            if (array_key_exists($from, $input)) {
                $input[$to] = $input[$from];
                unset($input[$from]);
            }
        }

        // Normaliza valores específicos
        if (isset($input['sort'])) {
            $input['sort'] = Str::snake($input['sort']);
        }

        if (isset($input['direction'])) {
            $input['direction'] = Str::snake($input['direction']);
        }

        $this->replace($input);
    }

    /**
     * Regras de validação dos parâmetros de listagem.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Paginação
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],

            // Busca textual
            'search' => ['sometimes', 'string', 'max:255'],

            // Ordenação
            'sort' => [
                'sometimes',
                Rule::in(['name']),
            ],
            'direction' => [
                'sometimes',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }

    /**
     * Normaliza os dados de entrada.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if ($key !== null || ! is_array($data)) {
            return $data;
        }

        // Defaults
        $data['per_page'] = $data['per_page'] ?? 10;
        $data['sort'] = $data['sort'] ?? 'name';
        $data['direction'] = $data['direction'] ?? 'asc';

        return $data;
    }
}
