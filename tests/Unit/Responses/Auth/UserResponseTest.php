<?php

namespace Tests\Unit\Responses\Auth;

use App\Http\Responses\Auth\UserResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResponseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa resposta de criação bem-sucedida
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_created_response(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
        ]);

        // Act
        $response = UserResponse::created($user);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Usuário criado com sucesso!', $response->getData(true)['message']);
        
        // Verifica estrutura dos dados
        $data = $response->getData(true)['data'];
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('age', $data);
        $this->assertArrayHasKey('birth_date', $data);
        $this->assertArrayHasKey('phone', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('created_at', $data);
        
        $this->assertEquals($user->id, $data['id']);
        $this->assertEquals($user->name, $data['name']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertEquals($user->age, $data['age']);
        $this->assertEquals($user->birth_date, $data['birth_date']);
        $this->assertEquals($user->phone, $data['phone']);
    }

    /**
     * Testa resposta com usuário normal
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_created_response_for_normal_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals($user->id, $data['data']['id']);
        $this->assertEquals($user->email, $data['data']['email']);
    }

    /**
     * Testa resposta com usuário de idade mínima
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_created_response_for_minimum_age_user(): void
    {
        // Arrange
        $user = User::factory()->age(1)->create();

        // Act
        $response = UserResponse::created($user);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals(1, $data['data']['age']);
    }

    /**
     * Testa resposta com usuário de idade avançada
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_created_response_for_advanced_age_user(): void
    {
        // Arrange
        $user = User::factory()->age(120)->create();

        // Act
        $response = UserResponse::created($user);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals(120, $data['data']['age']);
    }

    /**
     * Testa resposta com nome contendo caracteres especiais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_created_response_for_special_characters_name(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'João Silva García',
        ]);

        // Act
        $response = UserResponse::created($user);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('João Silva García', $data['data']['name']);
    }

    /**
     * Testa resposta com telefone internacional
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_created_response_for_international_phone(): void
    {
        // Arrange
        $user = User::factory()->create([
            'phone' => '+55 11 98765-4321',
        ]);

        // Act
        $response = UserResponse::created($user);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('+55 11 98765-4321', $data['data']['phone']);
    }

    /**
     * Testa se campos sensíveis não são expostos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_not_expose_sensitive_fields(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);
        $data = $response->getData(true)['data'];

        // Assert
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
        $this->assertArrayNotHasKey('updated_at', $data);
    }

    /**
     * Testa se created_at está presente e formatado
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_include_created_at_field(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);
        $data = $response->getData(true)['data'];

        // Assert
        $this->assertArrayHasKey('created_at', $data);
        $this->assertNotNull($data['created_at']);
        $this->assertEquals($user->created_at->toISOString(), $data['created_at']);
    }

    /**
     * Testa se todos os campos esperados estão presentes
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_contain_all_expected_fields(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);
        $data = $response->getData(true)['data'];

        // Assert
        $expectedFields = ['id', 'name', 'age', 'birth_date', 'phone', 'email', 'created_at'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $data);
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

        // Act
        $response = UserResponse::created($user);
        $data = $response->getData(true)['data'];

        // Assert
        $expectedFields = ['id', 'name', 'age', 'birth_date', 'phone', 'email', 'created_at'];
        $actualFields = array_keys($data);
        
        foreach ($actualFields as $field) {
            $this->assertContains($field, $expectedFields, "Campo inesperado: {$field}");
        }
    }

    /**
     * Testa se o content type é JSON
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_json_content_type(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);

        // Assert
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * Testa se a mensagem está correta
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_correct_message(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);

        // Assert
        $data = $response->getData(true);
        $this->assertEquals('Usuário criado com sucesso!', $data['message']);
    }

    /**
     * Testa se o ID do usuário está correto
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_correct_user_id(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);

        // Assert
        $data = $response->getData(true);
        $this->assertEquals($user->id, $data['data']['id']);
    }

    /**
     * Testa se o email do usuário está correto
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_correct_user_email(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Act
        $response = UserResponse::created($user);

        // Assert
        $data = $response->getData(true);
        $this->assertEquals('test@example.com', $data['data']['email']);
    }

    /**
     * Testa se o nome do usuário está correto
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_correct_user_name(): void
    {
        // Arrange
        $user = User::factory()->create(['name' => 'Test User']);

        // Act
        $response = UserResponse::created($user);

        // Assert
        $data = $response->getData(true);
        $this->assertEquals('Test User', $data['data']['name']);
    }

    /**
     * Testa se a resposta pode ser convertida para array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_be_convertible_to_array(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);
        $array = $response->getData(true);

        // Assert
        $this->assertIsArray($array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertEquals('Usuário criado com sucesso!', $array['message']);
        $this->assertIsArray($array['data']);
    }

    /**
     * Testa se a resposta funciona com usuário recém-criado
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_work_with_freshly_created_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $freshUser = User::find($user->id);

        // Act
        $response = UserResponse::created($freshUser);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals($freshUser->id, $data['data']['id']);
    }

    /**
     * Testa se a estrutura JSON está correta
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_have_correct_json_structure(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = UserResponse::created($user);

        // Assert
        $data = $response->getData(true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
        
        $expectedFields = ['id', 'name', 'age', 'birth_date', 'phone', 'email', 'created_at'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $data['data']);
        }
    }
}


