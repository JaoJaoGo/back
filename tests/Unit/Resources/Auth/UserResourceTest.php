<?php

namespace Tests\Unit\Resources\Auth;

use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa transformação do resource para array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_user_to_array(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
        ]);

        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($user->id, $result['id']);
        $this->assertEquals($user->name, $result['name']);
        $this->assertEquals($user->age, $result['age']);
        $this->assertEquals($user->birth_date, $result['birth_date']);
        $this->assertEquals($user->phone, $result['phone']);
        $this->assertEquals($user->email, $result['email']);
        $this->assertEquals($user->created_at->toISOString(), $result['created_at']);
    }

    /**
     * Testa se campos sensíveis não são incluídos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_not_include_sensitive_fields(): void
    {
        // Arrange
        $user = User::factory()->create();
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('remember_token', $result);
        $this->assertArrayNotHasKey('updated_at', $result);
    }

    /**
     * Testa transformação com usuário normal
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_normal_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($user->id, $result['id']);
        $this->assertEquals($user->email, $result['email']);
    }

    /**
     * Testa transformação com idade mínima
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_user_with_minimum_age(): void
    {
        // Arrange
        $user = User::factory()->age(1)->create();
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertEquals(1, $result['age']);
    }

    /**
     * Testa transformação com idade avançada
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_user_with_advanced_age(): void
    {
        // Arrange
        $user = User::factory()->age(120)->create();
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertEquals(120, $result['age']);
    }

    /**
     * Testa transformação com nome contendo caracteres especiais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_user_with_special_characters(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'João Silva García',
        ]);
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertEquals('João Silva García', $result['name']);
    }

    /**
     * Testa transformação com telefone internacional
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_user_with_international_phone(): void
    {
        // Arrange
        $user = User::factory()->create([
            'phone' => '+55 11 98765-4321',
        ]);
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertEquals('+55 11 98765-4321', $result['phone']);
    }

    /**
     * Testa transformação com email em maiúsculas
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_user_with_uppercase_email(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'Test@Example.com',
        ]);
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertEquals('Test@Example.com', $result['email']);
    }

    /**
     * Testa se created_at está no formato correto
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_format_created_at_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertIsString($result['created_at']);
        $this->assertEquals($user->created_at->toISOString(), $result['created_at']);
    }

    /**
     * Testa se birth_date está no formato correto
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_format_birth_date_correctly(): void
    {
        // Arrange
        $birthDate = '1999-01-01';
        $user = User::factory()->create(['birth_date' => $birthDate]);
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertEquals($birthDate, $result['birth_date']);
    }

    /**
     * Testa transformação com usuário sem dados opcionais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_user_with_minimal_data(): void
    {
        // Arrange
        $user = User::factory()->create([
            'remember_token' => null,
        ]);
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
    }

    /**
     * Testa se resource pode ser serializado para JSON
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_be_serializable_to_json(): void
    {
        // Arrange
        $user = User::factory()->create();
        $resource = new UserResource($user);

        // Act
        $json = $resource->toJson();

        // Assert
        $this->assertIsString($json);
        $this->assertStringContainsString('"id":' . $user->id, $json);
        $this->assertStringContainsString('"email":"' . $user->email . '"', $json);
    }

    /**
     * Testa se resource pode ser usado em response
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_work_in_response(): void
    {
        // Arrange
        $user = User::factory()->create();
        $resource = new UserResource($user);

        // Act
        $response = response()->json($resource);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals($user->id, $responseData['id']);
        $this->assertEquals($user->email, $responseData['email']);
        $this->assertEquals($user->name, $responseData['name']);
    }

    /**
     * Testa transformação com múltiplos usuários
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_transform_multiple_users(): void
    {
        // Arrange
        $users = User::factory()->count(3)->create();
        $resources = $users->map(fn ($user) => new UserResource($user));

        // Act
        $results = $resources->map(fn ($resource) => $resource->toArray(request()));

        // Assert
        $this->assertCount(3, $results);
        foreach ($results as $index => $result) {
            $this->assertEquals($users[$index]->id, $result['id']);
            $this->assertEquals($users[$index]->email, $result['email']);
        }
    }

    /**
     * Testa se todos os campos esperados estão presentes
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_contain_all_expected_fields(): void
    {
        // Arrange
        $user = User::factory()->create();
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $expectedFields = ['id', 'name', 'age', 'birth_date', 'phone', 'email', 'created_at'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $result);
        }
    }

    /**
     * Testa se não há campos extras inesperados
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_not_contain_unexpected_fields(): void
    {
        // Arrange
        $user = User::factory()->create();
        $resource = new UserResource($user);

        // Act
        $result = $resource->toArray(request());

        // Assert
        $expectedFields = ['id', 'name', 'age', 'birth_date', 'phone', 'email', 'created_at'];
        $actualFields = array_keys($result);
        
        foreach ($actualFields as $field) {
            $this->assertContains($field, $expectedFields, "Campo inesperado: {$field}");
        }
    }
}


