<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Post
 *
 * Model responsável por representar a entidade Post
 * no sistema.
 *
 * Este model:
 * - Representa conteúdos publicados por usuários
 * - Suporta exclusão lógica (Soft Deletes)
 * - Define atributos mass-assignable e casts
 * - Declara relacionamentos Eloquent
 *
 * @property int $id
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $tags
 * @property string $content
 * @property int $user_id
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 *
 * @package App\Models
 */
class Post extends Model
{
    /**
     * Traits utilizados pelo model.
     *
     * - SoftDeletes: exclusão lógica de registros
     * - HasFactory: suporte a factories
     */
    use SoftDeletes, HasFactory;
    
    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'subtitle',
        'tags',
        'content',
        'user_id',
        'image',
    ];

    /**
     * Define os casts automáticos dos atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Relacionamento: um post pertence a um usuário.
     *
     * @return BelongsTo Relacionamento com a entidade User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
