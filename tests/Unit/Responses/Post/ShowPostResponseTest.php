<?php

namespace Tests\Unit\Responses\Post;

use App\Http\Responses\Post\ShowPostResponse;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowPostResponseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_return_post_response(): void
    {
        $post = Post::factory()->create();

        $response = ShowPostResponse::fromModel($post);

        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($post->id, $data['data']['id']);
    }
}
