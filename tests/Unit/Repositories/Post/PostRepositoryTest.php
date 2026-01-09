<?php

namespace Tests\Unit\Repositories\Post;

use App\Http\Repositories\Post\PostRepository;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new PostRepository();
    }

    #[Test]
    public function it_should_paginate_posts_with_default_per_page(): void
    {
        Post::factory()->count(15)->create();

        $paginator = $this->repository->paginate([]);

        $this->assertEquals(10, $paginator->perPage());
        $this->assertEquals(15, $paginator->total());
        $this->assertCount(10, $paginator->items());
    }

    #[Test]
    public function it_should_filter_by_author_when_provided(): void
    {
        Post::factory()->count(2)->create(['author' => 'Alice']);
        Post::factory()->count(3)->create(['author' => 'Bob']);

        $paginator = $this->repository->paginate([
            'author' => 'Alice',
            'per_page' => 10,
        ]);

        $this->assertEquals(2, $paginator->total());
        foreach ($paginator->items() as $post) {
            $this->assertEquals('Alice', $post->author);
        }
    }

    #[Test]
    public function it_should_filter_by_search_without_breaking_author_filter(): void
    {
        Post::factory()->create([
            'author' => 'Alice',
            'title' => 'Hello World',
            'subtitle' => 'Subtitle A',
        ]);

        Post::factory()->create([
            'author' => 'Bob',
            'title' => 'Another',
            'subtitle' => 'Hello From Subtitle',
        ]);

        $paginator = $this->repository->paginate([
            'author' => 'Alice',
            'search' => 'Hello',
            'per_page' => 10,
        ]);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('Alice', $paginator->items()[0]->author);
    }

    #[Test]
    public function it_should_filter_by_tags_when_provided(): void
    {
        $laravel = Tag::factory()->create(['name' => 'laravel']);
        $php = Tag::factory()->create(['name' => 'php']);

        $post1 = Post::factory()->create();
        $post1->tags()->attach([$laravel->id]);

        $post2 = Post::factory()->create();
        $post2->tags()->attach([$php->id]);

        $paginator = $this->repository->paginate([
            'tags' => ['laravel'],
            'per_page' => 10,
        ]);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals($post1->id, $paginator->items()[0]->id);
    }

    #[Test]
    public function it_should_sort_by_title_ascending(): void
    {
        Post::factory()->create(['title' => 'B']);
        Post::factory()->create(['title' => 'A']);

        $paginator = $this->repository->paginate([
            'sort' => 'title',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $this->assertEquals('A', $paginator->items()[0]->title);
        $this->assertEquals('B', $paginator->items()[1]->title);
    }
}
