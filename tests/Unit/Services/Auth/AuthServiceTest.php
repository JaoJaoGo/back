<?php

namespace Tests\Unit\Services\Auth;

use App\Http\Services\Auth\AuthService;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

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

        $credentials = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertEquals($user->email, $result['user']->email);
        $this->assertEquals($user->name, $result['user']->name);
    }

    /**
     * Testa login com email incorreto
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_throw_exception_with_wrong_email(): void
    {
        // Arrange
        $password = 'password123';
        User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $credentials = [
            'email' => 'wrong@example.com',
            'password' => $password,
        ];

        // Act & Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Email ou senha inválidos');
        
        $this->authService->login($credentials);
    }

    /**
     * Testa login com senha incorreta
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_throw_exception_with_wrong_password(): void
    {
        // Arrange
        $user = User::factory()->create();

        $credentials = [
            'email' => $user->email,
            'password' => 'wrong_password',
        ];

        // Act & Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Email ou senha inválidos');
        
        $this->authService->login($credentials);
    }

    /**
     * Testa login com email inexistente
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_throw_exception_with_nonexistent_email(): void
    {
        // Arrange
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        // Act & Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Email ou senha inválidos');
        
        $this->authService->login($credentials);
    }

    /**
     * Testa login com credenciais vazias
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_throw_exception_with_empty_credentials(): void
    {
        // Arrange
        $credentials = [
            'email' => '',
            'password' => '',
        ];

        // Act & Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Email ou senha inválidos');
        
        $this->authService->login($credentials);
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

        $credentials = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($user->id, $result['user']->id);
    }

    /**
     * Testa login com múltiplos usuários existentes
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_login_with_multiple_users_existing(): void
    {
        // Arrange
        $password = 'password123';
        $users = User::factory()->count(3)->create([
            'password' => Hash::make($password),
        ]);

        $targetUser = $users->first();
        $credentials = [
            'email' => $targetUser->email,
            'password' => $password,
        ];

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($targetUser->id, $result['user']->id);
        $this->assertEquals($targetUser->email, $result['user']->email);
    }

    /**
     * Testa login com senha em formato hash complexo
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_login_with_complex_hashed_password(): void
    {
        // Arrange
        $password = 'ComplexP@ssw0rd!2024';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($user->id, $result['user']->id);
    }

    /**
     * Testa se o usuário fica autenticado após login bem-sucedido
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_authenticate_user_after_successful_login(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => $password,
        ];

        // Act
        $this->authService->login($credentials);

        // Assert
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals($user->id, Auth::guard('web')->id());
    }

    /**
     * Testa login com email case-sensitive
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

        // Test with exact case
        $credentials = [
            'email' => 'Test@Example.com',
            'password' => $password,
        ];

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($user->id, $result['user']->id);
    }
}


