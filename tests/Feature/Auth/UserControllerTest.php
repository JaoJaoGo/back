<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa registro de usuário com dados válidos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_register_user_with_valid_data(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'age',
                    'birth_date',
                    'phone',
                    'email',
                    'created_at',
                ],
            ]);

        $this->assertEquals('Usuário criado com sucesso!', $response->json('message'));
        $this->assertEquals($payload['name'], $response->json('data.name'));
        $this->assertEquals($payload['email'], $response->json('data.email'));
        $this->assertEquals($payload['age'], $response->json('data.age'));
        $this->assertEquals($payload['birth_date'], $response->json('data.birth_date'));
        $this->assertEquals($payload['phone'], $response->json('data.phone'));

        // Verifica se o usuário foi criado no banco
        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'name' => $payload['name'],
        ]);
    }

    /**
     * Testa registro com nome faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_name(): void
    {
        // Arrange
        $payload = [
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Testa registro com idade faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_age(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age']);
    }

    /**
     * Testa registro com data de nascimento faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_birth_date(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);
    }

    /**
     * Testa registro com telefone faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_phone(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /**
     * Testa registro com email faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_email(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa registro com senha faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_missing_password(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Testa registro com email inválido
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_invalid_email(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'invalid-email',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa registro com email duplicado
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_duplicate_email(): void
    {
        // Arrange
        $existingUser = User::factory()->create();

        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => $existingUser->email,
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa registro com idade inválida (texto)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_text_age(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 'twenty-five',
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age']);
    }

    /**
     * Testa registro com idade zero
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_zero_age(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 0,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age']);
    }

    /**
     * Testa registro com idade negativa
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_negative_age(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => -5,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age']);
    }

    /**
     * Testa registro com data de nascimento inválida
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_invalid_birth_date(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => 'invalid-date',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);
    }

    /**
     * Testa registro com senha fraca
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_weak_password(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => '123',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Testa registro quando limite máximo de usuários é atingido
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_error_when_max_users_limit_reached(): void
    {
        // Arrange
        User::factory()->count(2)->create();

        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Número máximo de usuários atingidos.',
            ]);
    }

    /**
     * Testa registro com idade mínima válida
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_register_with_minimum_age(): void
    {
        // Arrange
        $payload = [
            'name' => 'Young User',
            'age' => 1,
            'birth_date' => '2023-01-01',
            'phone' => '+1234567890',
            'email' => 'young@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertEquals(1, $response->json('data.age'));
    }

    /**
     * Testa registro com idade avançada
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_register_with_advanced_age(): void
    {
        // Arrange
        $payload = [
            'name' => 'Senior User',
            'age' => 120,
            'birth_date' => '1904-01-01',
            'phone' => '+1234567890',
            'email' => 'senior@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertEquals(120, $response->json('data.age'));
    }

    /**
     * Testa registro com nome contendo caracteres especiais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_register_with_special_characters_in_name(): void
    {
        // Arrange
        $payload = [
            'name' => 'João Silva',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'joao@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertEquals('João Silva', $response->json('data.name'));
    }

    /**
     * Testa registro com telefone internacional
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_register_with_international_phone(): void
    {
        // Arrange
        $payload = [
            'name' => 'International User',
            'age' => 30,
            'birth_date' => '1994-01-01',
            'phone' => '+55 11 98765-4321',
            'email' => 'international@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertEquals('+55 11 98765-4321', $response->json('data.phone'));
    }

    /**
     * Testa se senha é armazenada como hash
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_store_password_as_hash(): void
    {
        // Arrange
        $plainPassword = 'Password123!';
        $payload = [
            'name' => 'Hash Test User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'hashtest@example.com',
            'password' => $plainPassword,
        ];

        // Act
        $this->postJson('/api/register', $payload);

        // Assert
        $user = User::where('email', $payload['email'])->first();
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    /**
     * Testa se campos sensíveis não são expostos na resposta
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_not_expose_sensitive_fields_in_response(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertArrayNotHasKey('password', $response->json('data'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data'));
    }

    /**
     * Testa se timestamps são criados automaticamente
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_timestamps(): void
    {
        // Arrange
        $payload = [
            'name' => 'Timestamp User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'timestamp@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertArrayHasKey('created_at', $response->json('data'));
        $this->assertNotNull($response->json('data.created_at'));
    }

    /**
     * Testa registro exatamente no limite de usuários
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_register_when_exactly_at_limit(): void
    {
        // Arrange
        User::factory()->create(); // 1 usuário existente

        $payload = [
            'name' => 'Last User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'last@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertEquals(2, User::count());
    }

    /**
     * Testa registro com todos os campos vazios
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_validation_error_with_all_empty_fields(): void
    {
        // Arrange
        $payload = [
            'name' => '',
            'age' => '',
            'birth_date' => '',
            'phone' => '',
            'email' => '',
            'password' => '',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'age', 'birth_date', 'phone', 'email', 'password']);
    }
}
