<?php

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    private LoginRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new LoginRequest();
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
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);

        // Verifica regras do email
        $emailRules = $rules['email'];
        $this->assertContains('required', $emailRules);
        $this->assertContains('email', $emailRules);
        $this->assertContains('max:255', $emailRules);

        // Verifica regras da senha
        $passwordRules = $rules['password'];
        $this->assertContains('required', $passwordRules);
        $this->assertContains('string', $passwordRules);
        $this->assertContains('max:255', $passwordRules);
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
        $this->assertEquals('O email deve ser obrigatório', $messages['email.required']);
        $this->assertEquals('O email deve ser válido', $messages['email.email']);
        $this->assertEquals('O email deve ter no máximo 255 caracteres', $messages['email.max']);
        $this->assertEquals('A senha deve ser obrigatória', $messages['password.required']);
        $this->assertEquals('A senha deve ser uma string', $messages['password.string']);
        $this->assertEquals('A senha deve ter no máximo 255 caracteres', $messages['password.max']);
    }

    /**
     * Testa validação com dados válidos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_valid_data(): void
    {
        // Arrange
        $validData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
        $this->assertEmpty($validator->errors()->all());
    }

    /**
     * Testa validação com email faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_missing_email(): void
    {
        // Arrange
        $invalidData = [
            'password' => 'password123',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
        $this->assertEquals('O email deve ser obrigatório', $validator->errors()->first('email'));
    }

    /**
     * Testa validação com senha faltando
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_missing_password(): void
    {
        // Arrange
        $invalidData = [
            'email' => 'test@example.com',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
        $this->assertEquals('A senha deve ser obrigatória', $validator->errors()->first('password'));
    }

    /**
     * Testa validação com email inválido
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_invalid_email(): void
    {
        // Arrange
        $invalidData = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
        $this->assertEquals('O email deve ser válido', $validator->errors()->first('email'));
    }

    /**
     * Testa validação com email muito longo
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_too_long_email(): void
    {
        // Arrange
        $invalidData = [
            'email' => str_repeat('a', 256) . '@example.com',
            'password' => 'password123',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
        $this->assertEquals('O email deve ter no máximo 255 caracteres', $validator->errors()->first('email'));
    }

    /**
     * Testa validação com senha muito longa
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_too_long_password(): void
    {
        // Arrange
        $invalidData = [
            'email' => 'test@example.com',
            'password' => str_repeat('a', 256),
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
        $this->assertEquals('A senha deve ter no máximo 255 caracteres', $validator->errors()->first('password'));
    }

    /**
     * Testa validação com senha não string
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_non_string_password(): void
    {
        // Arrange
        $invalidData = [
            'email' => 'test@example.com',
            'password' => 123456,
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
        $this->assertEquals('A senha deve ser uma string', $validator->errors()->first('password'));
    }

    /**
     * Testa validação com campos vazios
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_empty_fields(): void
    {
        // Arrange
        $invalidData = [
            'email' => '',
            'password' => '',
        ];

        // Act
        $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
        $this->assertEquals('O email deve ser obrigatório', $validator->errors()->first('email'));
        $this->assertEquals('A senha deve ser obrigatória', $validator->errors()->first('password'));
    }

    /**
     * Testa validação com email no limite de caracteres
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_max_length_email(): void
    {
        // Arrange
        $email = str_repeat('a', 240) . '@example.com'; // 240 + 12 = 252 caracteres (< 255)
        $validData = [
            'email' => $email,
            'password' => 'password123',
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Testa validação com senha no limite de caracteres
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_with_max_length_password(): void
    {
        // Arrange
        $password = str_repeat('a', 255);
        $validData = [
            'email' => 'test@example.com',
            'password' => $password,
        ];

        // Act
        $validator = validator($validData, $this->request->rules(), $this->request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * Testa validação com email válido em diferentes formatos
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_validate_various_valid_email_formats(): void
    {
        // Arrange
        $validEmails = [
            'simple@example.com',
            'very.common@example.com',
            'disposable.style.email.with+symbol@example.com',
            'other.email-with-hyphen@example.com',
            'fully-qualified-domain@example.com',
            'user.name+tag+sorting@example.com',
            'x@example.com',
            'example-indeed@strange-example.com',
            'admin@mailserver1',
            'example@s.example',
            'mailhost!username@example.org',
            'user%example.com@example.org',
        ];

        foreach ($validEmails as $email) {
            $validData = [
                'email' => $email,
                'password' => 'password123',
            ];

            // Act
            $validator = validator($validData, $this->request->rules(), $this->request->messages());

            // Assert
            $this->assertTrue($validator->passes(), "Email should be valid: {$email}");
        }
    }

    /**
     * Testa validação com emails inválidos comuns
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_fail_validation_with_common_invalid_emails(): void
    {
        // Arrange
        $invalidEmails = [
            'plainaddress',
            '@missingdomain.com',
            'username@.com',
            'username@-example.com',
            'username@example..com',
            'username@example.com-',
            'username@example.com_',
            'username@.com',
        ];

        foreach ($invalidEmails as $email) {
            $invalidData = [
                'email' => $email,
                'password' => 'password123',
            ];

            // Act
            $validator = validator($invalidData, $this->request->rules(), $this->request->messages());

            // Assert
            $this->assertFalse($validator->passes(), "Email should be invalid: {$email}");
            $this->assertEquals('O email deve ser válido', $validator->errors()->first('email'));
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
        $expectedFields = ['email', 'password'];
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


