<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\UserResource;
use App\Http\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthController
 * 
 * Controller responsável pelo fluxo de autenticação da API.
 * 
 * Este controller atua como uma camada fina (thin controller),
 * delegando toda a lógica de negócio para o {@see AuthService},
 * mantendo-se responsável apenas por:
 * - Receber e tipar requisições
 * - Retornar respostas HTTP padronizadas (JSON)
 * 
 * Endpoints disponíveis:
 * - POST /login    -> Autenticação do usuário
 * - GET /me        -> Retorna os dados do usuário autenticado
 * - POST /logout   -> Invalida o token de acesso atual
 * 
 * @package App\Http\Controllers\Auth
 */
class AuthController extends Controller
{
    /**
     * Cria uma nova instância do controller.
     * 
     * O {@see AuthService} é injetado via container
     * para manter baixo acoplamento e facilitar testes.
     * 
     * @param AuthService $authService Serviço responsável pela autenticação
     */
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Realiza o login do usuário.
     * 
     * Valida as credenciais através do {@see LoginRequest}
     * e delega o processo de autenticação ao {@see AuthService}.
     * 
     * A resposta normalmente contém:
     * - Dados do usuário autenticado
     * 
     * @param LoginRequest $request Request contendo as credenciais validadas
     * 
     * @return JsonResponse Resposta JSON com os dados de autenticação
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json(
            $this->authService->login($request->validated())
        );
    }

    /**
     * Retorna os dados do usuário autenticado.
     * 
     * Utiliza o usuário presente no contexto da requisição
     * (definido pelo middleware de autenticação).
     * 
     * Os dados são transformados através do {@see UserResource}
     * para garantir consistência na resposta da API.
     * 
     * @param Request $request Requisição HTTP atual
     * 
     * @return JsonResponse Resposta JSON contendo o usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Realiza o logout do usuário autenticado.
     * 
     * Invalida o token de acesso atual (ex: token Sanctum),
     * encerrando a sessão da API.
     * 
     * @param Request $request Requisição HTTP atual
     * 
     * @return JsonResponse Resposta JSON confirmando o logout
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
        ]);
    }
}
