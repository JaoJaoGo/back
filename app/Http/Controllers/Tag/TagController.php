<?php

namespace App\Http\Controllers\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\ListTagsRequest;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Responses\Tag\ListTagsResponse;
use App\Http\Responses\Tag\ShowTagResponse;
use App\Http\Responses\Tag\StoreTagResponse;
use App\Http\Responses\Tag\UpdateTagResponse;
use App\Http\responses\Tag\DeleteTagResponse;
use App\Http\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

/**
 * Class TagController
 * 
 * Controller responsável pelo gerenciamento de tags
 * no contexto de listagem.
 * 
 * Atua como uma camada fina (thin controller), sendo responsável apenas por:
 * - Receber e validar requicições HTTP
 * - Delegar a lógica de negócio para {@see TagService}
 * - Retornar respostas HTTP padronizadas via {@see ListTagsResponse}
 * - Retornar respostas HTTP padronizadas via {@see ShowTagResponse}
 * - Retornar respostas HTTP padronizadas via {@see StoreTagResponse}
 * 
 * @package App\Http\Controllers\Tag
 */
class TagController extends Controller
{
    /**
     * Injeta o {@see TagService} para que o controller
     * possa delegar a lógica de negócio para ele.
     */
    public function __construct(
        protected TagService $tagService
    ) {}

    /**
     * Lista as tags.
     * 
     * @param ListTagsRequest $request Request contendo os dados validados
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo as tags
     */
    public function index(ListTagsRequest $request): JsonResponse
    {
        $tags = $this->tagService->list($request->validated());

        return ListTagsResponse::fromPaginator($tags);
    }

    /**
     * Exibe uma tag.
     * 
     * @param int $id ID da tag
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo a tag
     */
    public function show(int $id): JsonResponse
    {
        $tag = $this->tagService->find($id);

        return ShowTagResponse::fromModel($tag);
    }

    /**
     * Cria uma nova tag.
     * 
     * @param StoreTagRequest $request Request contendo os dados validados
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo a tag
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tagService->create($request->validated());

        return StoreTagResponse::fromModel($tag);
    }

    /**
     * Atualiza uma tag.
     * 
     * @param UpdateTagRequest $request Request contendo os dados validados
     * @param int $id ID da tag
     * 
     * @return JsonResponse Resposta HTTP padronizada contendo a tag
     */
    public function update(UpdateTagRequest $request, int $id): JsonResponse
    {
        $tag = $this->tagService->update($id, $request->validated());

        return UpdateTagResponse::fromModel($tag);
    }

    /**
     * Remove uma tag.
     * 
     * @param int $id ID da tag
     * 
     * @return JsonResponse Resposta HTTP padronizada
     */
    public function destroy(int $id): JsonResponse
    {
        $this->tagService->delete($id);

        return DeleteTagResponse::success();
    }
}
