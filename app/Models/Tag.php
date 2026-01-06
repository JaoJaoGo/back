<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Tag
 *
 * Model responsável por representar a entidade Tag
 * no sistema.
 *
 * Este model:
 * - Representa tags de posts
 * - Suporta exclusão lógica (Soft Deletes)
 * - Define atributos mass-assignable e casts
 * - Declara relacionamentos Eloquent
 *
 * @property int $id
 * @property string $name
 *
 * @property-read \App\Models\Post[] $posts
 *
 * @package App\Models
 */
class Tag extends Model
{
    /**
     * Traits utilizados pelo model.
     *
     * - HasFactory: suporte a factories
     */
    use HasFactory;

    /**
     * Nome da tabela no banco de dados.
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Relacionamento: uma tag pertence a muitos posts
     * 
     * @return BelongsToMany Relacionamento com a entidade Post
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
