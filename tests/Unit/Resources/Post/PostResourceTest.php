<?php

namespace Tests\Unit\Resources\Post;

use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_transform_post_for_show_context(): void
    {
        $post = Post::factory()->create([
            'subtitle' => 'Sub',
            'content' => 'Conteúdo',
            'image' => 'posts/test.jpg',
        ]);

        $post->load('tags');

        $resource = new PostResource($post);
        $data = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('subtitle', $data);
        $this->assertArrayHasKey('content', $data);
        $this->assertArrayHasKey('image', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);

        $this->assertEquals($post->id, $data['id']);
        $this->assertEquals($post->title, $data['title']);
        $this->assertEquals($post->subtitle, $data['subtitle']);
        $this->assertEquals($post->content, $data['content']);
        $this->assertEquals($post->image, $data['image']);
        $this->assertEquals($post->author, $data['author']);
        $this->assertEquals($post->created_at?->toISOString(), $data['createdAt']);
        $this->assertEquals($post->updated_at?->toISOString(), $data['updatedAt']);
    }
}
