<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'subtitle' => $this->faker->optional()->sentence(10),
            'content' => $this->faker->paragraphs(3, true),
            'image' => null,
            'author' => $this->faker->name(),
        ];
    }
}
