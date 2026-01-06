<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class LoginRequest
 *
 * Request responsável pela validação das credenciais
 * de autenticação do usuário.
 *
 * Centraliza as regras de validação do login,
 * mantendo o controller desacoplado de detalhes
 * de validação e garantindo reutilização das regras.
 *
 * @package App\Http\Requests\Auth
 */
class LoginRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a realizar esta requisição.
     *
     * Como este endpoint é público (login),
     * o acesso é liberado para qualquer solicitante.
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
     * Valida as credenciais básicas necessárias
     * para autenticação do usuário.
     *
     * Campos esperados:
     * - email: obrigatório, formato válido e tamanho máximo
     * - password: obrigatório e string
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
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
            'email.required' => 'O email deve ser obrigatório',
            'email.email' => 'O email deve ser válido',
            'email.max' => 'O email deve ter no máximo 255 caracteres',
            'password.required' => 'A senha deve ser obrigatória',
            'password.string' => 'A senha deve ser uma string',
            'password.max' => 'A senha deve ter no máximo 255 caracteres',
        ];
    }
}
