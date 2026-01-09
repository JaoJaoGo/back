<?php

namespace Tests\Feature\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeAuthRequest(): self
    {
        $user = User::factory()->create();

        return $this->withHeader('Origin', config('app.front_url'))
            ->withHeader('Accept', 'application/json')
            ->actingAs($user, 'web');
    }

    #[Test]
    public function it_should_require_authentication_for_posts_routes(): void
    {
        $this->getJson('/api/posts')->assertStatus(401);
        $this->getJson('/api/posts/1')->assertStatus(401);
        $this->postJson('/api/posts', [])->assertStatus(401);
        $this->putJson('/api/posts/1', [])->assertStatus(401);
        $this->deleteJson('/api/posts/1')->assertStatus(401);
    }

    #[Test]
    public function it_should_list_posts_with_pagination_meta(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->makeAuthRequest()->getJson('/api/posts');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'currentPage',
                    'perPage',
                    'total',
                    'lastPage',
                    'hasMorePages',
                ],
            ]);
    }

    #[Test]
    public function it_should_filter_posts_by_author(): void
    {
        Post::factory()->count(2)->create(['author' => 'Alice']);
        Post::factory()->count(1)->create(['author' => 'Bob']);

        $response = $this->makeAuthRequest()->getJson('/api/posts?author=Alice');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_should_filter_posts_by_search_and_keep_other_filters(): void
    {
        Post::factory()->create([
            'author' => 'Alice',
            'title' => 'Hello World',
            'subtitle' => 'Subtitle',
        ]);

        Post::factory()->create([
            'author' => 'Bob',
            'title' => 'Other',
            'subtitle' => 'Hello From Subtitle',
        ]);

        $response = $this->makeAuthRequest()->getJson('/api/posts?author=Alice&search=Hello');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Alice', $response->json('data.0.author'));
    }

    #[Test]
    public function it_should_filter_posts_by_tags(): void
    {
        $php = Tag::factory()->create(['name' => 'php']);
        $laravel = Tag::factory()->create(['name' => 'laravel']);

        $post1 = Post::factory()->create();
        $post1->tags()->attach([$php->id]);

        $post2 = Post::factory()->create();
        $post2->tags()->attach([$laravel->id]);

        $response = $this->makeAuthRequest()->getJson('/api/posts?tags[]=php');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($post1->id, $response->json('data.0.id'));
    }

    #[Test]
    public function it_should_validate_list_filters(): void
    {
        $response = $this->makeAuthRequest()->getJson('/api/posts?per_page=101');

        $response->assertStatus(422);
    }

    #[Test]
    public function it_should_show_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->makeAuthRequest()->getJson('/api/posts/'.$post->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'subtitle',
                    'content',
                    'image',
                    'author',
                    'createdAt',
                    'updatedAt',
                ],
            ]);

        $this->assertSame($post->id, $response->json('data.id'));
    }

    #[Test]
    public function it_should_return_404_when_showing_nonexistent_post(): void
    {
        $response = $this->makeAuthRequest()->getJson('/api/posts/999999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_should_create_post_with_tags_and_normalize_tags(): void
    {
        Storage::fake('public');

        $payload = [
            'title' => 'My Post',
            'subtitle' => 'Sub',
            'content' => 'Conteúdo',
            'author' => 'Author',
            'tags' => [' PHP ', 'php', 'Laravel'],
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ];

        $response = $this->makeAuthRequest()->post('/api/posts', $payload);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'title', 'author'],
            ]);

        $postId = $response->json('data.id');
        $this->assertDatabaseHas('posts', ['id' => $postId, 'title' => 'My Post']);

        $this->assertDatabaseHas('tags', ['name' => 'php']);
        $this->assertDatabaseHas('tags', ['name' => 'laravel']);

        $imagePath = Post::findOrFail($postId)->image;
        Storage::disk('public')->assertExists($imagePath);
    }

    #[Test]
    public function it_should_validate_create_post_payload(): void
    {
        $response = $this->makeAuthRequest()->postJson('/api/posts', [
            'title' => '',
            'content' => '',
            'author' => '',
            'tags' => [],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_should_update_post_and_remove_image(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('old.jpg')->store('posts', 'public');

        $post = Post::factory()->create([
            'image' => $path,
        ]);

        $response = $this->makeAuthRequest()->putJson('/api/posts/'.$post->id, [
            'remove_image' => true,
        ]);

        $response->assertStatus(200);

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($post->fresh()->image);
    }

    #[Test]
    public function it_should_update_post_and_replace_image(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('old.jpg')->store('posts', 'public');

        $post = Post::factory()->create([
            'image' => $oldPath,
        ]);

        $response = $this->makeAuthRequest()->put('/api/posts/'.$post->id, [
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertStatus(200);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($post->fresh()->image);
    }

    #[Test]
    public function it_should_return_404_when_updating_nonexistent_post(): void
    {
        $response = $this->makeAuthRequest()->putJson('/api/posts/999999', [
            'title' => 'X',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_should_validate_update_payload(): void
    {
        $post = Post::factory()->create();

        $response = $this->makeAuthRequest()->putJson('/api/posts/'.$post->id, [
            'remove_image' => 'yes',
            'tags' => [],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_should_delete_post(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('delete.jpg')->store('posts', 'public');

        $post = Post::factory()->create([
            'image' => $path,
        ]);

        $tag = Tag::factory()->create(['name' => 't1']);
        $post->tags()->attach([$tag->id]);

        $response = $this->makeAuthRequest()->deleteJson('/api/posts/'.$post->id);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Post removido com sucesso.']);

        Storage::disk('public')->assertMissing($path);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id]);
    }

    #[Test]
    public function it_should_return_404_when_deleting_nonexistent_post(): void
    {
        $response = $this->makeAuthRequest()->deleteJson('/api/posts/999999');

        $response->assertStatus(404);
    }
}
