<?php

namespace Tests\Unit\Responses\Post;

use App\Http\Responses\Post\UpdatePostResponse;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePostResponseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_return_ok_response(): void
    {
        $post = Post::factory()->create();

        $response = UpdatePostResponse::fromModel($post);

        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Post atualizado com sucesso.', $data['message']);
        $this->assertEquals($post->id, $data['data']['id']);
    }
}
