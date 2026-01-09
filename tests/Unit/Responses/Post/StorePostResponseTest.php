<?php

namespace Tests\Unit\Responses\Post;

use App\Http\Responses\Post\StorePostResponse;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StorePostResponseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_return_created_response(): void
    {
        $post = Post::factory()->create();

        $response = StorePostResponse::fromModel($post);

        $this->assertEquals(201, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Post criado com sucesso', $data['message']);
        $this->assertEquals($post->id, $data['data']['id']);
    }
}
