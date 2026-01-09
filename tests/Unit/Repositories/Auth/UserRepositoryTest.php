<?php

namespace Tests\Unit\Repositories\Auth;

use App\Http\Repositories\Auth\UserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
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

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['age'], $user->age);
        $this->assertEquals($userData['birth_date'], $user->birth_date);
        $this->assertEquals($userData['phone'], $user->phone);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name' => $userData['name'],
        ]);
    }

    /**
     * Testa contagem de usuários quando não existem usuários
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_zero_when_no_users_exist(): void
    {
        // Act
        $count = $this->userRepository->count();

        // Assert
        $this->assertEquals(0, $count);
    }

    /**
     * Testa contagem de usuários quando existem usuários
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_correct_user_count(): void
    {
        // Arrange
        User::factory()->count(5)->create();

        // Act
        $count = $this->userRepository->count();

        // Assert
        $this->assertEquals(5, $count);
    }

    /**
     * Testa criação de múltiplos usuários
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_multiple_users(): void
    {
        // Arrange
        $usersData = [
            [
                'name' => 'User One',
                'age' => 25,
                'birth_date' => '1999-01-01',
                'phone' => '+1234567890',
                'email' => 'user1@example.com',
                'password' => 'password123',
            ],
            [
                'name' => 'User Two',
                'age' => 30,
                'birth_date' => '1994-01-01',
                'phone' => '+0987654321',
                'email' => 'user2@example.com',
                'password' => 'password456',
            ],
        ];

        // Act
        $createdUsers = [];
        foreach ($usersData as $userData) {
            $createdUsers[] = $this->userRepository->create($userData);
        }

        // Assert
        $this->assertCount(2, $createdUsers);
        foreach ($createdUsers as $index => $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals($usersData[$index]['email'], $user->email);
        }
        $this->assertEquals(2, $this->userRepository->count());
    }

    /**
     * Testa criação de usuário com dados mínimos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_minimal_data(): void
    {
        // Arrange
        $userData = [
            'name' => 'Minimal User',
            'age' => 1,
            'birth_date' => '2023-01-01',
            'phone' => '1',
            'email' => 'minimal@example.com',
            'password' => 'pass',
        ];

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['age'], $user->age);
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);
    }

    /**
     * Testa criação de usuário com dados máximos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_maximum_data(): void
    {
        // Arrange
        $userData = [
            'name' => str_repeat('A', 255),
            'age' => 999,
            'birth_date' => '1025-01-01',
            'phone' => str_repeat('1', 255),
            'email' => 'verylongemailaddress' . str_repeat('a', 200) . '@example.com',
            'password' => str_repeat('x', 255),
        ];

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['age'], $user->age);
        $this->assertEquals($userData['phone'], $user->phone);
        $this->assertEquals($userData['email'], $user->email);
    }

    /**
     * Testa se o usuário criado tem ID
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_id(): void
    {
        // Arrange
        $userData = [
            'name' => 'User With ID',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'withid@example.com',
            'password' => 'password123',
        ];

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertNotNull($user->id);
        $this->assertIsInt($user->id);
        $this->assertGreaterThan(0, $user->id);
    }

    /**
     * Testa se timestamps são criados automaticamente
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_create_user_with_timestamps(): void
    {
        // Arrange
        $userData = [
            'name' => 'Timestamp User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'timestamp@example.com',
            'password' => 'password123',
        ];

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->updated_at);
    }

    /**
     * Testa contagem após criação e exclusão de usuários
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_update_count_after_user_deletion(): void
    {
        // Arrange
        User::factory()->count(3)->create();
        $this->assertEquals(3, $this->userRepository->count());

        // Act
        User::first()->delete();

        // Assert
        $this->assertEquals(2, $this->userRepository->count());
    }

    /**
     * Testa criação de usuário com email duplicado (deve falhar no nível do banco)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_to_create_user_with_duplicate_email(): void
    {
        // Arrange
        $email = 'duplicate@example.com';
        User::factory()->create(['email' => $email]);

        $userData = [
            'name' => 'Duplicate User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => $email,
            'password' => 'password123',
        ];

        // Act & Assert
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->userRepository->create($userData);
    }

    /**
     * Testa criação de usuário com senha em texto plano (deve ser hash)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_store_password_as_hash(): void
    {
        // Arrange
        $plainPassword = 'plaintext_password';
        $userData = [
            'name' => 'Hash Test User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'hashtest@example.com',
            'password' => $plainPassword,
        ];

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($plainPassword, $user->password));
    }
}


