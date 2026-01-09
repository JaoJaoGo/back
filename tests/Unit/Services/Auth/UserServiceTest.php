<?php

namespace Tests\Unit\Services\Auth;

use App\Http\Repositories\Auth\UserRepository;
use App\Http\Services\Auth\UserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->userService = new UserService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Testa criação de usuário com dados válidos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_valid_data(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = new User($userData);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(1);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userData) {
                return $data['name'] === $userData['name'] &&
                    $data['age'] === $userData['age'] &&
                    $data['birth_date'] === $userData['birth_date'] &&
                    $data['phone'] === $userData['phone'] &&
                    $data['email'] === $userData['email'] &&
                    Hash::check($userData['password'], $data['password']);
            }))
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userData['name'], $result->name);
        $this->assertEquals($userData['email'], $result->email);
    }

    /**
     * Testa criação de usuário quando limite máximo é atingido
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_throw_exception_when_max_users_limit_reached(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(2);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Número máximo de usuários atingidos.');

        $this->userService->create($userData);
    }

    /**
     * Testa criação de usuário com senha fraca (deve ser criptografada)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_hash_user_password(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'simple_password',
        ];

        $user = new User($userData);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userData) {
                return Hash::check($userData['password'], $data['password']);
            }))
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
    }

    /**
     * Testa criação de usuário com idade mínima válida
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_minimum_age(): void
    {
        // Arrange
        $userData = [
            'name' => 'Young User',
            'age' => 1,
            'birth_date' => '2023-01-01',
            'phone' => '+1234567890',
            'email' => 'young@example.com',
            'password' => 'password123',
        ];

        $user = new User($userData);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->age);
    }

    /**
     * Testa criação de usuário com idade avançada
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_advanced_age(): void
    {
        // Arrange
        $userData = [
            'name' => 'Senior User',
            'age' => 120,
            'birth_date' => '1904-01-01',
            'phone' => '+1234567890',
            'email' => 'senior@example.com',
            'password' => 'password123',
        ];

        $user = new User($userData);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(120, $result->age);
    }

    /**
     * Testa criação de usuário com telefone internacional
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_international_phone(): void
    {
        // Arrange
        $userData = [
            'name' => 'International User',
            'age' => 30,
            'birth_date' => '1994-01-01',
            'phone' => '+55 11 98765-4321',
            'email' => 'international@example.com',
            'password' => 'password123',
        ];

        $user = new User($userData);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('+55 11 98765-4321', $result->phone);
    }

    /**
     * Testa criação de usuário com nome com caracteres especiais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_special_characters_in_name(): void
    {
        // Arrange
        $userData = [
            'name' => 'João Silva',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'joao@example.com',
            'password' => 'password123',
        ];

        $user = new User($userData);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('João Silva', $result->name);
    }

    /**
     * Testa criação de usuário exatamente no limite (2 usuários)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_allow_creation_when_exactly_at_limit(): void
    {
        // Arrange
        $userData = [
            'name' => 'Last User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'last@example.com',
            'password' => 'password123',
        ];

        $user = new User($userData);
        $user->id = 2;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(1); // 1 usuário existente, permite criar o segundo

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
    }

    /**
     * Testa se o repositório é chamado com os dados corretos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_call_repository_with_correct_data(): void
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $expectedData = $userData;
        $expectedData['password'] = Hash::make($userData['password']);

        $user = new User($userData);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userData) {
                return $data['name'] === $userData['name'] &&
                       $data['email'] === $userData['email'] &&
                       $data['age'] === $userData['age'] &&
                       $data['birth_date'] === $userData['birth_date'] &&
                       $data['phone'] === $userData['phone'] &&
                       Hash::check($userData['password'], $data['password']);
            }))
            ->andReturn($user);

        // Act
        $result = $this->userService->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->id);
    }
}


