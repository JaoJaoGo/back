<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\ListPostsRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Responses\Post\ListPostsResponse;
use App\Http\Responses\Post\ShowPostResponse;
use App\Http\Responses\Post\StorePostResponse;
use App\Http\Responses\Post\UpdatePostResponse;
use App\Http\Responses\Post\DeletePostResponse;
use App\Http\Services\Post\PostService;
use Illuminate\Http\JsonResponse;

/**
 * Class PostController
 * 
 * Controller responsável pelo gerenciamento de posts
 * no contexto de listagem.
 * 
 * Atua como uma camada fina (thin controller), sendo responsável apenas por:
 * - Receber e validar requicições HTTP
 * - Delegar a lógica de negócio para {@see PostService}
 * - Retornar respostas HTTP padronizadas via {@see ListPostsResponse}
 * - Retornar respostas HTTP padronizadas via {@see ShowPostResponse}
 * - Retornar respostas HTTP padronizadas via {@see StorePostResponse}
 * 
 * @package App\Http\Controllers\Post
 */
class PostController extends Controller
{
    /**
     * Injeta o {@see PostService} para que o controller
     * possa delegar a lógica de negócio para ele.
     */
    public function __construct(
        protected PostService $postService
    ) {}

    /**
     * Lista os posts.
     * 
     * @param ListPostsRequest $request Request contendo os dados validados
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo os posts
     */
    public function index(ListPostsRequest $request): JsonResponse
    {
        $posts = $this->postService->list($request->validated());

        return ListPostsResponse::fromPaginator($posts);
    }

    /**
     * Exibe um post.
     * 
     * @param int $id ID do post
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo o post
     */
    public function show(int $id): JsonResponse
    {
        $post = $this->postService->find($id);

        return ShowPostResponse::fromModel($post);
    }

    /**
     * Cria um novo post.
     * 
     * @param StorePostRequest $request Request contendo os dados validados
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo o post
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->create($request->validated());

        return StorePostResponse::fromModel($post);
    }

    /**
     * Atualiza um post.
     * 
     * @param UpdatePostRequest $request Request contendo os dados validados
     * @param int $id ID do post
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo o post
     */
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = $this->postService->update($id, $request->validated());

        return UpdatePostResponse::fromModel($post);
    }

    /**
     * Remove um post.
     * 
     * @param int $id ID do post
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public function destroy(int $id): JsonResponse
    {
        $this->postService->delete($id);

        return DeletePostResponse::success();
    }
}
