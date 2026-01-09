<?php

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\StoreUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private StoreUserRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new StoreUserRequest();
    }

    /**
     * Testa se a autorização é permitida
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_authorize_request(): void
    {
        // Act
        $result = $this->request->authorize();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Testa regras de validação
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_correct_validation_rules(): void
    {
        // Act
        $rules = $this->request->rules();

        // Assert
        $this->assertIsArray($rules);
        
        $expectedFields = ['name', 'age', 'birth_date', 'phone', 'email', 'password'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules);
        }

        // Verifica regras do name
        $nameRules = $rules['name'];
        $this->assertContains('required', $nameRules);
        $this->assertContains('string', $nameRules);

        // Verifica regras do age
        $ageRules = $rules['age'];
        $this->assertContains('required', $ageRules);
        $this->assertContains('integer', $ageRules);
        $this->assertContains('min:1', $ageRules);

        // Verifica regras do birth_date
        $birthDateRules = $rules['birth_date'];
        $this->assertContains('required', $birthDateRules);
        $this->assertContains('date', $birthDateRules);

        // Verifica regras do phone
        $phoneRules = $rules['phone'];
        $this->assertContains('required', $phoneRules);
        $this->assertContains('string', $phoneRules);

        // Verifica regras do email
        $emailRules = $rules['email'];
        $this->assertContains('required', $emailRules);
        $this->assertContains('email', $emailRules);
        $this->assertContains('unique:users,email', $emailRules);

        // Verifica regras do password
        $passwordRules = $rules['password'];
        $this->assertContains('required', $passwordRules);
        // Password::defaults() retorna um objeto Password, não uma string
    }

    /**
     * Testa mensagens de validação personalizadas
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_return_custom_validation_messages(): void
    {
        // Act
        $messages = $this->request->messages();

        // Assert
        $this->assertIsArray($messages);
        $this->assertEquals('O nome é obrigatório', $messages['name.required']);
        $this->assertEquals('A idade é obrigatória', $messages['age.required']);
        $this->assertEquals('A idade deve ser um número inteiro', $messages['age.integer']);
        $this->assertEquals('A idade deve ser pelo menos 1', $messages['age.min']);
        $this->assertEquals('A data de nascimento é obrigatória', $messages['birth_date.required']);
        $this->assertEquals('O telefone é obrigatório', $messages['phone.required']);
        $this->assertEquals('O e-mail é obrigatório', $messages['email.required']);
        $this->assertEquals('O e-mail deve ser válido', $messages['email.email']);
        $this->assertEquals('O e-mail já está em uso', $messages['email.unique']);
        $this->assertEquals('A senha é obrigatória', $messages['password.required']);
        $this->assertEquals('A senha deve ser forte', $messages['password.password']);
    }

    /**
     * Testa validação com dados válidos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_valid_data(): void
    {
        // Arrange
        $validData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
        $this->assertEmpty($validator->errors()->all());
    }

    /**
     * Testa validação com email duplicado
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_duplicate_email(): void
    {
        // Arrange
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $invalidData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
        $this->assertEquals('O e-mail já está em uso', $validator->errors()->first('email'));
    }

    /**
     * Testa validação com idade inválida (texto)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_text_age(): void
    {
        // Arrange
        $invalidData = [
            'name' => 'John Doe',
            'age' => 'twenty-five',
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('age', $validator->errors()->messages());
        $this->assertEquals('A idade deve ser um número inteiro', $validator->errors()->first('age'));
    }

    /**
     * Testa validação com idade zero
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_zero_age(): void
    {
        // Arrange
        $invalidData = [
            'name' => 'John Doe',
            'age' => 0,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('age', $validator->errors()->messages());
        $this->assertEquals('A idade deve ser pelo menos 1', $validator->errors()->first('age'));
    }

    /**
     * Testa validação com idade negativa
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_negative_age(): void
    {
        // Arrange
        $invalidData = [
            'name' => 'John Doe',
            'age' => -5,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('age', $validator->errors()->messages());
        $this->assertEquals('A idade deve ser pelo menos 1', $validator->errors()->first('age'));
    }

    /**
     * Testa validação com data de nascimento inválida
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_invalid_birth_date(): void
    {
        // Arrange
        $invalidData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => 'invalid-date',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('birth_date', $validator->errors()->messages());
    }

    /**
     * Testa validação com senha fraca
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_weak_password(): void
    {
        // Arrange
        $invalidData = [
            'name' => 'John Doe',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
            'password' => '123',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
        $this->assertEquals('A senha deve ser forte', $validator->errors()->first('password'));
    }

    /**
     * Testa validação com todos os campos faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_all_missing_fields(): void
    {
        // Arrange
        $invalidData = [];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        
        $errors = $validator->errors()->messages();
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('age', $errors);
        $this->assertArrayHasKey('birth_date', $errors);
        $this->assertArrayHasKey('phone', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);

        $this->assertEquals('O nome é obrigatório', $errors['name'][0]);
        $this->assertEquals('A idade é obrigatória', $errors['age'][0]);
        $this->assertEquals('A data de nascimento é obrigatória', $errors['birth_date'][0]);
        $this->assertEquals('O telefone é obrigatório', $errors['phone'][0]);
        $this->assertEquals('O e-mail é obrigatório', $errors['email'][0]);
        $this->assertEquals('A senha é obrigatória', $errors['password'][0]);
    }

    /**
     * Testa validação com idade mínima válida
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_minimum_age(): void
    {
        // Arrange
        $validData = [
            'name' => 'Young User',
            'age' => 1,
            'birth_date' => '2023-01-01',
            'phone' => '+1234567890',
            'email' => 'young@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Testa validação com idade avançada
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_advanced_age(): void
    {
        // Arrange
        $validData = [
            'name' => 'Senior User',
            'age' => 120,
            'birth_date' => '1904-01-01',
            'phone' => '+1234567890',
            'email' => 'senior@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Testa validação com nome contendo caracteres especiais
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_special_characters_in_name(): void
    {
        // Arrange
        $validData = [
            'name' => 'João Silva García',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'joao@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Testa validação com telefone internacional
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_international_phone(): void
    {
        // Arrange
        $validData = [
            'name' => 'International User',
            'age' => 30,
            'birth_date' => '1994-01-01',
            'phone' => '+55 11 98765-4321',
            'email' => 'international@example.com',
            'password' => 'Password123!',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Testa validação com senha forte
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_strong_password(): void
    {
        // Arrange
        $validData = [
            'name' => 'Strong Password User',
            'age' => 25,
            'birth_date' => '1999-01-01',
            'phone' => '+1234567890',
            'email' => 'strong@example.com',
            'password' => 'VeryStr0ng!P@ssw0rd#2024',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Testa validação com diferentes formatos de data válidos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_various_date_formats(): void
    {
        // Arrange
        $validDates = [
            '1999-01-01',
            '2000-12-31',
            '2023-05-15',
            '1995-02-28',
        ];

        foreach ($validDates as $date) {
            $validData = [
                'name' => 'Date Test User',
                'age' => 25,
                'birth_date' => $date,
                'phone' => '+1234567890',
                'email' => 'date' . uniqid() . '@example.com',
                'password' => 'Password123!',
            ];

            // Act
            $validator = validator($validData, $this->request->rules(), $this->request->messages());

            // Assert
            $this->assertTrue($validator->passes(), "Date should be valid: {$date}");
        }
    }

    /**
     * Testa validação com formatos de data inválidos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_invalid_date_formats(): void
    {
        // Arrange
        $invalidDates = [
            '1999-13-01',  // Mês inválido
            '1999-01-32',  // Dia inválido
            '1999-02-30',  // Fevereiro com 30 dias
            'not-a-date',
            'invalid-date',
        ];

        foreach ($invalidDates as $date) {
            $invalidData = [
                'name' => 'Invalid Date User',
                'age' => 25,
                'birth_date' => $date,
                'phone' => '+1234567890',
                'email' => 'invalid' . uniqid() . '@example.com',
                'password' => 'Password123!',
            ];

            // Act
            $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

            // Assert
            $this->assertFalse($validator->passes(), "Date should be invalid: {$date}");
            $this->assertArrayHasKey('birth_date', $validator->errors()->messages());
        }
    }

    /**
     * Testa se todos os campos esperados estão nas regras
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_contain_all_expected_fields_in_rules(): void
    {
        // Act
        $rules = $this->request->rules();

        // Assert
        $expectedFields = ['name', 'age', 'birth_date', 'phone', 'email', 'password'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules);
        }

        // Verifica se não há campos extras
        $actualFields = array_keys($rules);
        $this->assertEquals(count($expectedFields), count($actualFields));
        foreach ($actualFields as $field) {
            $this->assertContains($field, $expectedFields);
        }
    }
}


