<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa login com credenciais válidas
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_login_with_valid_credentials(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'age',
                    'birth_date',
                    'phone',
                    'email',
                    'created_at',
                ],
            ]);

        $this->assertEquals($user->id, $response->json('user.id'));
        $this->assertEquals($user->email, $response->json('user.email'));
        $this->assertEquals($user->name, $response->json('user.name'));
    }

    /**
     * Testa login com email inválido
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_with_invalid_email(): void
    {
        // Arrange
        $payload = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa login com email inexistente
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_with_nonexistent_email(): void
    {
        // Arrange
        $payload = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email ou senha inválidos',
            ]);
    }

    /**
     * Testa login com senha incorreta
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_with_wrong_password(): void
    {
        // Arrange
        $user = User::factory()->create();

        $payload = [
            'email' => $user->email,
            'password' => 'wrong_password',
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email ou senha inválidos',
            ]);
    }

    /**
     * Testa login com campos faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_fields(): void
    {
        // Arrange
        $payload = [];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Testa login com email faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_email(): void
    {
        // Arrange
        $payload = [
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa login com senha faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_password(): void
    {
        // Arrange
        $payload = [
            'email' => 'test@example.com',
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Testa login com campos vazios
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_empty_fields(): void
    {
        // Arrange
        $payload = [
            'email' => '',
            'password' => '',
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Testa login com email muito longo
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_too_long_email(): void
    {
        // Arrange
        $payload = [
            'email' => str_repeat('a', 256) . '@example.com',
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa login com senha muito longa
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_too_long_password(): void
    {
        // Arrange
        $payload = [
            'email' => 'test@example.com',
            'password' => str_repeat('a', 256),
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Testa endpoint /me com usuário autenticado
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_authenticated_user_data(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Autentica o usuário via sessão (guard web)
        $this->actingAs($user);

        // Act
        $response = $this->getJson('/api/me');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'age',
                    'birth_date',
                    'phone',
                    'email',
                    'created_at',
                ],
            ]);

        $this->assertEquals($user->id, $response->json('user.id'));
        $this->assertEquals($user->email, $response->json('user.email'));
        $this->assertEquals($user->name, $response->json('user.name'));
    }

    /**
     * Testa endpoint /me sem autenticação
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_when_me_without_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/me');

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Testa logout com usuário autenticado
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_logout_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->withHeader('Origin', config('app.front_url'))->actingAs($user, 'web')->postJson('/api/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout realizado com sucesso',
            ]);

        // Verifica se o usuário foi desautenticado
        $this->assertFalse(Auth::guard('web')->check());
    }

    /**
     * Testa logout sem autenticação
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_when_logout_without_authentication(): void
    {
        // Act
        $response = $this->postJson('/api/logout');

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Testa login com usuário normal
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_login_with_normal_user(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['user']);

        $this->assertEquals($user->id, $response->json('user.id'));
    }

    /**
     * Testa login com senha contendo caracteres especiais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_login_with_special_characters_password(): void
    {
        // Arrange
        $password = 'P@ssw0rd!2024#';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['user']);
    }

    /**
     * Testa se usuário permanece autenticado após login
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_keep_user_authenticated_after_login(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $this->postJson('/api/login', $payload);

        // Assert
        $this->getJson('/api/me')->assertStatus(200);
    }

    /**
     * Testa login case-sensitive no email
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_handle_case_sensitive_email(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'Test@Example.com',
            'password' => Hash::make($password),
        ]);

        // Test com case exato
        $payload = [
            'email' => 'Test@Example.com',
            'password' => $password,
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['user']);
    }

    /**
     * Testa se campos sensíveis não são expostos na resposta
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_not_expose_sensitive_fields_in_response(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $response = $this->postJson('/api/login', $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('password', $response->json('user'));
        $this->assertArrayNotHasKey('remember_token', $response->json('user'));
    }
}
