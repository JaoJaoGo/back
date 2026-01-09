<?php

namespace Tests\Unit\Requests\Post;

use App\Http\Requests\Post\ListPostsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListPostsRequestTest extends TestCase
{
    use RefreshDatabase;

    private ListPostsRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new ListPostsRequest();
    }

    private function validateAndGetValidated(array $query): array
    {
        $request = ListPostsRequest::create('/', 'GET', $query);
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

        $this->assertArrayHasKey('page', $rules);
        $this->assertArrayHasKey('per_page', $rules);
        $this->assertArrayHasKey('search', $rules);
        $this->assertArrayHasKey('author', $rules);
        $this->assertArrayHasKey('tags', $rules);
        $this->assertArrayHasKey('tags.*', $rules);
        $this->assertArrayHasKey('sort', $rules);
        $this->assertArrayHasKey('direction', $rules);

        $this->assertContains('sometimes', $rules['page']);
        $this->assertContains('integer', $rules['page']);
        $this->assertContains('min:1', $rules['page']);

        $this->assertContains('sometimes', $rules['per_page']);
        $this->assertContains('integer', $rules['per_page']);
        $this->assertContains('min:1', $rules['per_page']);
        $this->assertContains('max:100', $rules['per_page']);

        $this->assertContains('sometimes', $rules['tags']);
        $this->assertContains('array', $rules['tags']);

        $this->assertContains('string', $rules['tags.*']);
        $this->assertContains('max:50', $rules['tags.*']);
    }

    #[Test]
    public function it_should_map_aliases_and_normalize_sort_and_direction_in_prepare_for_validation(): void
    {
        $validated = $this->validateAndGetValidated([
            'perPage' => 5,
            'sortBy' => 'createdAt',
            'sortDirection' => 'asc',
        ]);

        $this->assertEquals(5, $validated['per_page']);
        $this->assertEquals('created_at', $validated['sort']);
        $this->assertEquals('asc', $validated['direction']);
    }

    #[Test]
    public function it_should_apply_defaults_when_not_provided(): void
    {
        $validated = $this->validateAndGetValidated([]);

        $this->assertEquals(10, $validated['per_page']);
        $this->assertEquals('created_at', $validated['sort']);
        $this->assertEquals('desc', $validated['direction']);
    }

    #[Test]
    public function it_should_normalize_and_deduplicate_tags(): void
    {
        $validated = $this->validateAndGetValidated([
            'tags' => [' PHP ', 'php', 'Laravel', 'laravel '],
        ]);

        $this->assertEquals(['php', 'laravel'], $validated['tags']);
    }

    #[Test]
    public function it_should_fail_validation_when_per_page_is_greater_than_100(): void
    {
        $this->expectException(ValidationException::class);

        $this->validateAndGetValidated([
            'per_page' => 101,
        ]);
    }
}
