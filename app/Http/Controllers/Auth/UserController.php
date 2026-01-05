<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\StoreUserRequest;
use App\Http\Responses\UserResponse;
use App\Http\Services\UserService;

/**
 * Class UserController
 * 
 * Controller responsável pelo gerenciamento de usuários
 * no contexto de autenticação/registro.
 * 
 * Atua como uma camada fina (thin controller), sendo responsável apenas por:
 * - Receber e validar requicições HTTP
 * - Delegar a lógica de negócio para {@see UserService}
 * - Retornar respostas HTTP padronizadas via {@see UserResponse}
 * 
 * @package App\Http\Controllers\Auth
 */
class UserController extends Controller
{
    /**
     * Cria um novo usuário.
     * 
     * Os dados da requisição são validados através do
     * {@see StoreUserRequest} antes de serem enviados ao serviço.
     * 
     * A criação do usuário é delegada ao {@see UserService},
     * mantendo o controller desacoplado da regra de negócio.
     * 
     * @param StoreUserRequest $request Request contendo os dados validados do usuário
     * @param UserService $service Serviço responsável pela criação do usuário
     * 
     * @return UserResponse Resposta HTTP padronizada indicando sucesso na criação
     */
    public function store(StoreUserRequest $request, UserService $service)
    {
        $user = $service->create($request->validated());

        return UserResponse::created($user);
    }
}