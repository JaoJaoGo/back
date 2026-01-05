<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * Model responsável por representar a entidade Usuário
 * no sistema.
 *
 * Este model:
 * - Representa usuários autenticáveis
 * - Integra-se com Laravel Sanctum para autenticação via API
 * - Define atributos mass-assignable, ocultos e casts
 * - Declara relacionamentos Eloquent
 *
 * @property int $id
 * @property string $name
 * @property int $age
 * @property string $birth_date
 * @property string $phone
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    /**
     * Traits utilizados pelo model.
     *
     * - HasFactory: suporte a factories
     * - Notifiable: envio de notificações
     * - HasApiTokens: autenticação via API (Sanctum)
     *
     * @use HasFactory<\Database\Factories\UserFactory>
     */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'age',
        'birth_date',
        'phone',
        'email',
        'password',
    ];

    /**
     * Atributos ocultos durante a serialização (JSON/array).
     *
     * Evita exposição de dados sensíveis ou desnecessários
     * em respostas da API.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'birth_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Define os casts automáticos dos atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Relacionamento: um usuário pode possuir muitos posts.
     *
     * @return HasMany Relacionamento com a entidade Post
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
