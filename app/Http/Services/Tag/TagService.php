<?php

namespace App\Http\Services\Tag;

use App\Models\Tag;
use App\Http\Repositories\Tag\TagRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class TagService
 *
 * Serviço responsável pelas regras de negócio
 * relacionadas à criação de tags.
 *
 * Centraliza validações e políticas que não
 * pertencem ao controller ou ao repositório,
 * garantindo consistência e fácil manutenção.
 *
 * @package App\Http\Services\Tag
 */
class TagService
{
    /**
     * Cria uma nova instância do serviço.
     *
     * O {@see TagRepository} é injetado para
     * desacoplar o acesso a dados da regra
     * de negócio e facilitar testes.
     *
     * @param TagRepository $repository Repositório de tags
     */
    public function __construct(
        protected TagRepository $repository
    ) {}

    /**
     * Lista as tags com base nos filtros.
     *
     * @param array $filters Filtros de paginação
     *
     * @return LengthAwarePaginator
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    /**
     * Retorna uma tag pelo ID.
     * 
     * @param int $id ID da tag
     * 
     * @return Tag|ModelNotFoundException
     */
    public function find(int $id): Tag|ModelNotFoundException
    {
        $tag = $this->repository->findById($id);

        if (!$tag) {
            throw new ModelNotFoundException('Tag não encontrada.');
        }

        return $tag;
    }

    /**
     * Cria uma nova tag.
     * 
     * @param array $data Dados da tag
     * 
     * @return Tag
     */
    public function create(array $data): Tag
    {
        return DB::transaction(function () use ($data) {
            $tag = $this->repository->create($data);

            return $tag;
        });
    }

    /**
     * Atualiza uma tag.
     * 
     * @param int $id ID da tag
     * @param array $data Dados da tag
     * 
     * @return Tag
     */
    public function update(int $id, array $data): Tag
    {
        return DB::transaction(function () use ($id, $data) {
            $tag = $this->repository->findById($id);

            if (!$tag) {
                throw new ModelNotFoundException('Tag não encontrado.');
            }

            $this->repository->update($tag, $data);

            return $tag;
        });
    }

    /**
     * Remove uma tag.
     *
     * - Remove a tag
     *
     * @param int $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $tag = $this->repository->findById($id);

            if (!$tag) {
                throw new ModelNotFoundException('Tag não encontrada.');
            }

            $this->repository->delete($tag);
        });
    }
}