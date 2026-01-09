<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'age' => fake()->numberBetween(18, 80),
            'birth_date' => fake()->date('Y-m-d', '-18 years'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Create a user with specific age.
     */
    public function age(int $age): static
    {
        return $this->state(fn (array $attributes) => [
            'age' => $age,
            'birth_date' => now()->subYears($age)->format('Y-m-d'),
        ]);
    }

    /**
     * Create a minor user (under 18).
     */
    public function minor(): static
    {
        return $this->age(fake()->numberBetween(12, 17));
    }

    /**
     * Create an elderly user (over 65).
     */
    public function elderly(): static
    {
        return $this->age(fake()->numberBetween(66, 100));
    }

    /**
     * Create a user with a specific email.
     */
    public function email(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Create a user with a specific password.
     */
    public function password(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($password),
        ]);
    }
}
