<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa o fluxo completo: registro -> login -> acesso /me -> logout
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_complete_full_authentication_flow(): void
    {
        $origin = 'https://blogex.test';

        // 1) Registrar
        $registerData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/register', $registerData)
            ->assertStatus(201);

        // 2) Login (sessão SPA)
        $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/login', [
                'email' => $registerData['email'],
                'password' => $registerData['password'],
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['user']);

        // 3) /me deve funcionar (mesmo client, cookies persistem)
        $this
            ->withHeader('Origin', $origin)
            ->getJson('/api/me')
            ->assertStatus(200)
            ->assertJsonStructure(['user']);

        // 4) Logout
        $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/logout')
            ->assertStatus(200)
            ->assertJson(['message' => 'Logout realizado com sucesso']);

        // MUITO IMPORTANTE em testes: limpa cache do guard
        $this->app['auth']->forgetGuards();

        // 5) /me deve falhar após logout
        $this
            ->withHeader('Origin', $origin)
            ->getJson('/api/me')
            ->assertStatus(401);

        // garante que o guard web está deslogado
        $this->assertFalse(Auth::guard('web')->check());
    }

    /**
     * Testa fluxo com múltiplos usuários
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_handle_multiple_users_flow(): void
    {
        $origin = 'https://blogex.test';

        $usersData = [
            [
                'name' => 'User One',
                'age' => 25,
                'birth_date' => '1999-01-01',
                'phone' => '+1234567890',
                'email' => 'user1@example.com',
                'password' => 'Password123!',
            ],
            [
                'name' => 'User Two',
                'age' => 30,
                'birth_date' => '1994-01-01',
                'phone' => '+0987654321',
                'email' => 'user2@example.com',
                'password' => 'Password456!',
            ],
        ];

        // Registrar ambos
        foreach ($usersData as $userData) {
            $this
                ->withHeader('Origin', $origin)
                ->postJson('/api/register', $userData)
                ->assertStatus(201);
        }

        // Logar e validar /me para cada um (com logout no final)
        foreach ($usersData as $userData) {
            $this
                ->withHeader('Origin', $origin)
                ->postJson('/api/login', [
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                ])
                ->assertStatus(200);

            $this
                ->withHeader('Origin', $origin)
                ->getJson('/api/me')
                ->assertStatus(200)
                ->assertJsonPath('user.email', $userData['email']);

            $this
                ->withHeader('Origin', $origin)
                ->postJson('/api/logout')
                ->assertStatus(200);

            // MUITO IMPORTANTE em testes: limpa cache do guard
            $this->app['auth']->forgetGuards();

            // garante que caiu fora
            $this
                ->withHeader('Origin', $origin)
                ->getJson('/api/me')
                ->assertStatus(401);

            // Simula "novo browser" pro próximo usuário:
            $this->flushSession();
            $this->app['auth']->forgetGuards();
        }
    }

    /**
     * Testa fluxo com usuário normal
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_handle_normal_user_flow(): void
    {
        // Arrange
        $userData = [
            'name' => 'Normal User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'normal@example.com',
            'password' => 'Password123!',
        ];

        // Act - Registrar
        $registerResponse = $this->postJson('/api/register', $userData);
        $registerResponse->assertStatus(201);

        // Login - deve funcionar
        $loginResponse = $this->postJson('/api/login', [
            'email' => $userData['email'],
            'password' => $userData['password'],
        ]);

        $loginResponse->assertStatus(200);
        $this->assertArrayHasKey('user', $loginResponse->json());
    }

    /**
     * Testa fluxo quando limite de usuários é atingido
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_prevent_registration_when_limit_reached(): void
    {
        // Arrange - Criar 2 usuários (limite)
        User::factory()->count(2)->create();

        $newUserData = [
            'name' => 'Third User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'third@example.com',
            'password' => 'Password123!',
        ];

        // Act - Tentar registrar terceiro usuário
        $response = $this->postJson('/api/register', $newUserData);

        // Assert
        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Número máximo de usuários atingidos.',
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => $newUserData['email'],
        ]);
    }

    /**
     * Testa fluxo com senhas complexas
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_handle_complex_passwords(): void
    {
        $complexPassword = 'V3ryC0mpl3x!P@ssw0rd#2024$%^&*()';

        $userData = [
            'name' => 'Complex Password User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'complex@example.com',
            'password' => $complexPassword,
        ];

        // Registrar
        $registerResponse = $this->postJson('/api/register', $userData);
        $registerResponse->assertStatus(201);

        // Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => $userData['email'],
            'password' => $complexPassword,
        ]);

        $loginResponse->assertStatus(200);

        // Verificar se senha foi hashada corretamente
        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check($complexPassword, $user->password));
    }

    /**
     * Testa fluxo com dados internacionais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_handle_international_data(): void
    {
        $userData = [
            'name' => 'João Silva García',
            'age' => 30,
            'birth_date' => '1994-01-01',
            'phone' => '+55 11 98765-4321',
            'email' => 'joao.silva@exemplo.com.br',
            'password' => 'SenhaForte123!',
        ];

        // Registrar
        $registerResponse = $this->postJson('/api/register', $userData);
        $registerResponse
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'João Silva García',
                    'phone' => '+55 11 98765-4321',
                    'email' => 'joao.silva@exemplo.com.br',
                ],
            ]);

        // Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => $userData['email'],
            'password' => $userData['password'],
        ]);

        $loginResponse
            ->assertStatus(200)
            ->assertJson([
                'user' => [
                    'name' => 'João Silva García',
                    'email' => 'joao.silva@exemplo.com.br',
                ],
            ]);
    }

    /**
     * Testa fluxo com tentativas de login falhadas
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_handle_failed_login_attempts(): void
    {
        // Registrar usuário
        $userData = [
            'name' => 'Test User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        $this->postJson('/api/register', $userData);

        // Tentativas falhadas de login
        $failedAttempts = [
            ['email' => 'test@example.com', 'password' => 'wrong_password'],
            ['email' => 'wrong@example.com', 'password' => 'Password123!'],
            ['email' => 'wrong@example.com', 'password' => 'wrong_password'],
        ];

        foreach ($failedAttempts as $attempt) {
            $response = $this->postJson('/api/login', $attempt);
            $response
                ->assertStatus(401)
                ->assertJson([
                    'message' => 'Email ou senha inválidos',
                ]);
        }

        // Login correto deve funcionar
        $correctLogin = $this->postJson('/api/login', [
            'email' => $userData['email'],
            'password' => $userData['password'],
        ]);

        $correctLogin->assertStatus(200);
    }

    /**
     * Testa fluxo com acesso não autorizado (Sanctum SPA / sessão).
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_prevent_unauthorized_access(): void
    {
        $origin = 'http://localhost:5173';

        // 1) /me sem sessão
        $this
            ->withHeader('Origin', $origin)
            ->getJson('/api/me')
            ->assertStatus(401);

        // 2) /logout sem sessão
        $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/logout')
            ->assertStatus(401);

        // 3) Mesmo sem Origin (ainda deve ser 401)
        $this->getJson('/api/me')->assertStatus(401);
        $this->postJson('/api/logout')->assertStatus(401);
    }

    /**
     * Testa fluxo com sessão expirada/revogada
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_handle_revoked_session_after_logout(): void
    {
        $origin = config('app.front_url');

        // Registrar e fazer login
        $userData = [
            'name' => 'Session Test User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'session@example.com',
            'password' => 'Password123!',
        ];

        $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/register', $userData)
            ->assertStatus(201);

        $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/login', [
                'email' => $userData['email'],
                'password' => $userData['password'],
            ])
            ->assertStatus(200);

        $this->withHeader('Origin', $origin)->getJson('/api/me')->assertStatus(200);

        $this->withHeader('Origin', $origin)->postJson('/api/logout')->assertStatus(200);

        // MUITO IMPORTANTE em testes: limpa cache do guard
        $this->app['auth']->forgetGuards();

        $this->withHeader('Origin', $origin)->getJson('/api/me')->assertStatus(401);
    }

    /**
     * Testa consistência dos dados entre endpoints (SPA + Sanctum).
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_maintain_data_consistency_across_endpoints(): void
    {
        $origin = config('app.front_url');

        $userData = [
            'name' => 'Consistency Test User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'consistency@example.com',
            'password' => 'Password123!',
        ];

        // Registrar
        $registerResponse = $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/register', $userData);

        $registerResponse
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'age']]);

        $registerData = $registerResponse->json('data');

        // Login (SPA: cria sessão)
        $loginResponse = $this
            ->withHeader('Origin', $origin)
            ->postJson('/api/login', [
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);

        $loginResponse
            ->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'age']]);

        $loginData = $loginResponse->json('user');

        // Acessar /me (SPA: usa cookie de sessão)
        $meResponse = $this
            ->withHeader('Origin', $origin)
            ->getJson('/api/me');

        $meResponse
            ->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'age']]);

        $meData = $meResponse->json('user');

        // Verificar consistência
        $this->assertEquals($registerData['id'], $loginData['id']);
        $this->assertEquals($loginData['id'], $meData['id']);

        $this->assertEquals($registerData['name'], $loginData['name']);
        $this->assertEquals($loginData['name'], $meData['name']);

        $this->assertEquals($registerData['email'], $loginData['email']);
        $this->assertEquals($loginData['email'], $meData['email']);

        $this->assertEquals($registerData['age'], $loginData['age']);
        $this->assertEquals($loginData['age'], $meData['age']);
    }
}
