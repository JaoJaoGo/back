<?php

namespace Tests\Unit\Services\Post;

use App\Http\Repositories\Post\PostRepository;
use App\Http\Services\Post\PostService;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PostService(new PostRepository());
    }

    #[Test]
    public function it_should_create_post_with_tags_and_image(): void
    {
        Storage::fake('public');

        $data = [
            'title' => 'Post Title',
            'subtitle' => 'Sub',
            'content' => 'Conteúdo',
            'author' => 'Author',
            'tags' => ['php', 'laravel'],
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ];

        $post = $this->service->create($data);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertNotNull($post->image);
        Storage::disk('public')->assertExists($post->image);

        $this->assertCount(2, $post->tags);
        $this->assertDatabaseHas('tags', ['name' => 'php']);
        $this->assertDatabaseHas('tags', ['name' => 'laravel']);
        $this->assertDatabaseHas('post_tag', ['post_id' => $post->id]);
    }

    #[Test]
    public function it_should_throw_when_find_nonexistent_post(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->find(999999);
    }

    #[Test]
    public function it_should_throw_when_updating_nonexistent_post(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->update(999999, [
            'title' => 'X',
        ]);
    }

    #[Test]
    public function it_should_throw_when_deleting_nonexistent_post(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->delete(999999);
    }

    #[Test]
    public function it_should_update_post_tags_and_replace_image(): void
    {
        Storage::fake('public');

        $originalImage = UploadedFile::fake()->image('old.jpg');
        $originalPath = $originalImage->store('posts', 'public');

        $post = Post::factory()->create([
            'image' => $originalPath,
        ]);

        $oldTag = Tag::factory()->create(['name' => 'old']);
        $post->tags()->attach([$oldTag->id]);

        $updated = $this->service->update($post->id, [
            'tags' => ['new'],
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        Storage::disk('public')->assertMissing($originalPath);
        $this->assertNotNull($updated->image);
        Storage::disk('public')->assertExists($updated->image);

        $this->assertCount(1, $updated->tags);
        $this->assertEquals('new', $updated->tags->first()->name);
    }

    #[Test]
    public function it_should_remove_image_when_remove_image_flag_is_true(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('to-delete.jpg')->store('posts', 'public');

        $post = Post::factory()->create([
            'image' => $path,
        ]);

        $updated = $this->service->update($post->id, [
            'remove_image' => true,
        ]);

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($updated->image);
    }

    #[Test]
    public function it_should_delete_post_detach_tags_and_delete_image(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('delete.jpg')->store('posts', 'public');

        $post = Post::factory()->create([
            'image' => $path,
        ]);

        $tag = Tag::factory()->create(['name' => 't1']);
        $post->tags()->attach([$tag->id]);

        $this->service->delete($post->id);

        Storage::disk('public')->assertMissing($path);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id]);
    }
}
