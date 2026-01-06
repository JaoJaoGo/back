<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Class StoreUserRequest
 *
 * Request responsável pela validação dos dados
 * necessários para criação de um novo usuário.
 *
 * Centraliza as regras de validação do cadastro,
 * garantindo consistência entre diferentes pontos
 * de entrada (API, CLI, etc.).
 *
 * @package App\Http\Requests\Auth
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado
     * a realizar esta requisição.
     *
     * Como a criação de usuário pode ocorrer
     * em fluxos públicos ou administrativos,
     * a autorização é permitida.
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
     * - name: nome completo do usuário (obrigatório)
     * - age: idade do usuário (inteiro, mínimo 1)
     * - birth_date: data de nascimento válida
     * - phone: telefone de contato
     * - email: e-mail válido e único na tabela users
     * - password: senha forte conforme as regras padrão do Laravel
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'age' => ['required', 'integer', 'min:1'],
            'birth_date' => ['required', 'date'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
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
            'name.required' => 'O nome é obrigatório',
            'age.required' => 'A idade é obrigatória',
            'age.integer' => 'A idade deve ser um número inteiro',
            'age.min' => 'A idade deve ser pelo menos 1',
            'birth_date.required' => 'A data de nascimento é obrigatória',
            'phone.required' => 'O telefone é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'O e-mail deve ser válido',
            'email.unique' => 'O e-mail já está em uso',
            'password.required' => 'A senha é obrigatória',
            'password.password' => 'A senha deve ser forte',
        ];
    }
}