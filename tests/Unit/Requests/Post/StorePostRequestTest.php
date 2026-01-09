<?php

namespace Tests\Unit\Requests\Post;

use App\Http\Requests\Post\StorePostRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StorePostRequestTest extends TestCase
{
    use RefreshDatabase;

    private StorePostRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new StorePostRequest();
    }

    private function validateAndGetValidated(array $payload): array
    {
        $request = StorePostRequest::create('/', 'POST', $payload);
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
        $this->assertArrayHasKey('image', $rules);
        $this->assertArrayHasKey('author', $rules);
        $this->assertArrayHasKey('tags', $rules);
        $this->assertArrayHasKey('tags.*', $rules);

        $this->assertContains('required', $rules['title']);
        $this->assertContains('string', $rules['title']);
        $this->assertContains('max:255', $rules['title']);

        $this->assertContains('nullable', $rules['subtitle']);
        $this->assertContains('string', $rules['subtitle']);
        $this->assertContains('max:255', $rules['subtitle']);

        $this->assertContains('required', $rules['content']);
        $this->assertContains('string', $rules['content']);

        $this->assertContains('nullable', $rules['image']);
        $this->assertContains('image', $rules['image']);
        $this->assertContains('max:2048', $rules['image']);

        $this->assertContains('required', $rules['author']);
        $this->assertContains('string', $rules['author']);
        $this->assertContains('max:255', $rules['author']);

        $this->assertContains('required', $rules['tags']);
        $this->assertContains('array', $rules['tags']);
        $this->assertContains('min:1', $rules['tags']);

        $this->assertContains('required', $rules['tags.*']);
        $this->assertContains('string', $rules['tags.*']);
        $this->assertContains('max:50', $rules['tags.*']);
    }

    #[Test]
    public function it_should_define_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertIsArray($messages);
        $this->assertEquals('O título é obrigatório.', $messages['title.required']);
        $this->assertEquals('O conteúdo é obrigatório.', $messages['content.required']);
        $this->assertEquals('O autor é obrigatório.', $messages['author.required']);
        $this->assertEquals('Informe ao menos uma tag.', $messages['tags.required']);
        $this->assertEquals('As tags devem ser um array.', $messages['tags.array']);
        $this->assertEquals('O post deve possuir pelo menos uma tag.', $messages['tags.min']);
    }

    #[Test]
    public function it_should_normalize_and_deduplicate_tags_on_validated(): void
    {
        Storage::fake('public');

        $validated = $this->validateAndGetValidated([
            'title' => 'My Post',
            'subtitle' => 'Sub',
            'content' => 'Conteúdo',
            'author' => 'Author',
            'tags' => [' PHP ', 'php', 'Laravel', 'laravel '],
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $this->assertEquals(['php', 'laravel'], $validated['tags']);
        $this->assertArrayHasKey('image', $validated);
    }

    #[Test]
    public function it_should_fail_validation_when_tags_is_empty_array(): void
    {
        $this->expectException(ValidationException::class);

        $this->validateAndGetValidated([
            'title' => 'My Post',
            'content' => 'Conteúdo',
            'author' => 'Author',
            'tags' => [],
        ]);
    }
}
