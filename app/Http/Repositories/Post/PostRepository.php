<?php

namespace App\Http\Repositories\Post;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class PostRepository
 * 
 * Repositório responsável pelo acesso e persistência
 * de dados relacionados à entidade {@see Post}.
 * 
 * Esta camada abstrai o uso direto do Eloquent,
 * permitindo desacoplamento da regra de negócio,
 * maior testabilidade e facilidade de manutenção.
 * 
 * @package App\Http\Repositories\Post
 */
class PostRepository
{
    /**
     * Retorna uma paginação de posts com base nos filtros.
     * 
     * @param array $filters Filtros de paginação
     * 
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Post::query()
            ->with('tags')
            ->where('author', $filters['author'])
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) =>
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
            )
            ->when(
                $filters['tags'] ?? null,
                fn($q, $tags) =>
                    $q->whereHas('tags', 
                        fn ($t) => $t->whereIn('name', $tags)
                    )
            )
            ->orderBy(
                $filters['sort'] ?? 'created_at',
                $filters['direction'] ?? 'desc'
            )
            ->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Busca um post pelo ID.
     * 
     * @param int $id ID do post
     * 
     * @return Post|null
     */
    public function findById(int $id): ?Post
    {
        return Post::query()
            ->with('tags')
            ->find($id);
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
        return Post::create($data);
    }

    /**
     * Atualiza um post.
     * 
     * @param Post $post Post a ser atualizado
     * @param array $data Dados do post
     * 
     * @return Post
     */
    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        
        return $post;
    }

    /**
     * Deleta um post.
     * 
     * @param Post $post Post a ser deletado
     * 
     * @return void
     */
    public function delete(Post $post): void
    {
        $post->delete();
    }
}