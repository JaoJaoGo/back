<?php

namespace App\Http\Services\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Http\Repositories\Post\PostRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class PostService
 *
 * Serviço responsável pelas regras de negócio
 * relacionadas à criação de posts.
 *
 * Centraliza validações e políticas que não
 * pertencem ao controller ou ao repositório,
 * garantindo consistência e fácil manutenção.
 *
 * @package App\Http\Services\Post
 */
class PostService
{
    /**
     * Cria uma nova instância do serviço.
     *
     * O {@see PostRepository} é injetado para
     * desacoplar o acesso a dados da regra
     * de negócio e facilitar testes.
     *
     * @param PostRepository $repository Repositório de posts
     */
    public function __construct(
        protected PostRepository $repository
    ) {}

    /**
     * Lista os posts com base nos filtros.
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
     * Retorna um post pelo ID.
     * 
     * @param int $id ID do post
     * 
     * @return Post|ModelNotFoundException
     */
    public function find(int $id): Post|ModelNotFoundException
    {
        $post = $this->repository->findById($id);

        if (!$post) {
            throw new ModelNotFoundException('Post não encontrado.');
        }

        return $post;
    }

    /**
     * Cria um novo post.
     * 
     * @param array $data Dados do post
     * 
     * @return Post
     */
    public function create(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $tags = $data['tags'];
            unset($data['tags']);

            // Upload da imagem caso existir
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = $this->storeImage($data['image']);
            }

            $post = $this->repository->create($data);

            // Cria ou reutiliza tags
            $tagIds = collect($tags)->map(function (string $name) {
                return Tag::firstOrCreate(['name' => $name])->id;
            });

            $post->tags()->sync($tagIds);

            return $post->load('tags');
        });
    }

    /**
     * Atualiza um post.
     * 
     * @param int $id ID do post
     * @param array $data Dados do post
     * 
     * @return Post
     */
    public function update(int $id, array $data): Post
    {
        return DB::transaction(function () use ($id, $data) {
            $post = $this->repository->findById($id);

            if (!$post) {
                throw new ModelNotFoundException('Post não encontrado.');
            }

            // ---------- TAGS ----------
            if (isset($data['tags'])) {
                $tagIds = collect($data['tags'])->map(function (string $name) {
                    return Tag::firstOrCreate(['name' => $name])->id;
                });

                $post->tags()->sync($tagIds);
                unset($data['tags']);
            }

            // ---------- IMAGEM ----------
            // Remove imagem explicitamente
            if (!empty($data['remove_image']) && $post->image) {
                Storage::disk('public')->delete($post->image);
                $data['image'] = null;
            }

            // Substitui imagem
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }

                $data['image'] = $this->storeImage($data['image']);
            }

            unset($data['remove_image']);

            $this->repository->update($post, $data);

            return $post->load('tags');
        });
    }

    /**
     * Remove um post e seus recursos associados.
     *
     * - Apaga a imagem do storage (se existir)
     * - Remove vínculos da pivot post_tag
     * - Remove o post
     *
     * @param int $id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $post = $this->repository->findById($id);

            if (!$post) {
                throw new ModelNotFoundException('Post não encontrado.');
            }

            // Remove image do storage
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }

            /**
             * Remove vínculos com tags.
             * (redundante por causa do cascade, mas explícito)
             */
            $post->tags()->detach();

            $this->repository->delete($post);
        });
    }

    /**
     * Salva a imagem do post.
     * 
     * @param UploadedFile $file Arquivo da imagem
     * 
     * @return string Caminho da imagem
     */
    protected function storeImage(UploadedFile $file): string
    {
        return $file->store('posts', 'public');
    }
}