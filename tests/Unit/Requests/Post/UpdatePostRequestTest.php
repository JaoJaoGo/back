<?php

namespace Tests\Unit\Requests\Post;

use App\Http\Requests\Post\UpdatePostRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePostRequestTest extends TestCase
{
    use RefreshDatabase;

    private UpdatePostRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new UpdatePostRequest();
    }

    private function validateAndGetValidated(array $payload): array
    {
        $request = UpdatePostRequest::create('/', 'PUT', $payload);
        $request->setContainer(app())->setRedirector(app('redirect'));

        $request->validateResolved();

        return $request->validated();
    }

    #[Test]
    public function it_should_authorize_request(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    #[Test]
    public function it_should_define_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('subtitle', $rules);
        $this->assertArrayHasKey('content', $rules);
        $this->assertArrayHasKey('author', $rules);
        $this->assertArrayHasKey('image', $rules);
        $this->assertArrayHasKey('remove_image', $rules);
        $this->assertArrayHasKey('tags', $rules);
        $this->assertArrayHasKey('tags.*', $rules);

        $this->assertContains('sometimes', $rules['title']);
        $this->assertContains('string', $rules['title']);
        $this->assertContains('max:255', $rules['title']);

        $this->assertContains('nullable', $rules['subtitle']);
        $this->assertContains('string', $rules['subtitle']);
        $this->assertContains('max:255', $rules['subtitle']);

        $this->assertContains('sometimes', $rules['content']);
        $this->assertContains('string', $rules['content']);

        $this->assertContains('sometimes', $rules['author']);
        $this->assertContains('string', $rules['author']);
        $this->assertContains('max:255', $rules['author']);

        $this->assertContains('nullable', $rules['image']);
        $this->assertContains('image', $rules['image']);
        $this->assertContains('max:2048', $rules['image']);

        $this->assertContains('sometimes', $rules['remove_image']);
        $this->assertContains('boolean', $rules['remove_image']);

        $this->assertContains('sometimes', $rules['tags']);
        $this->assertContains('array', $rules['tags']);
        $this->assertContains('min:1', $rules['tags']);

        $this->assertContains('string', $rules['tags.*']);
        $this->assertContains('max:50', $rules['tags.*']);
    }

    #[Test]
    public function it_should_define_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertIsArray($messages);
        $this->assertEquals('As tags devem ser um array.', $messages['tags.array']);
        $this->assertEquals('O post deve possuir pelo menos uma tag.', $messages['tags.min']);
    }

    #[Test]
    public function it_should_normalize_and_deduplicate_tags_on_validated(): void
    {
        $validated = $this->validateAndGetValidated([
            'tags' => [' PHP ', 'php', 'Laravel', 'laravel '],
        ]);

        $this->assertEquals(['php', 'laravel'], $validated['tags']);
    }

    #[Test]
    public function it_should_fail_validation_when_remove_image_is_not_boolean(): void
    {
        $this->expectException(ValidationException::class);

        $this->validateAndGetValidated([
            'remove_image' => 'yes',
        ]);
    }
}
