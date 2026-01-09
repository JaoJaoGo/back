<?php

namespace Tests\Unit\Responses\Post;

use App\Http\Responses\Post\ListPostsResponse;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListPostsResponseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_return_paginated_posts_response(): void
    {
        Post::factory()->count(3)->create();

        $paginator = Post::query()->paginate(2);

        $response = ListPostsResponse::fromPaginator($paginator);

        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);

        $this->assertEquals(2, count($data['data']));
        $this->assertArrayHasKey('currentPage', $data['meta']);
        $this->assertArrayHasKey('perPage', $data['meta']);
        $this->assertArrayHasKey('total', $data['meta']);
        $this->assertArrayHasKey('lastPage', $data['meta']);
        $this->assertArrayHasKey('hasMorePages', $data['meta']);
    }
}
