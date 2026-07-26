<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'github_id' => (string) fake()->unique()->numberBetween(1, 100000000),
            'github_username' => fake()->unique()->userName(),
            'avatar' => fake()->imageUrl(),
            'remember_token' => Str::random(10),
        ];
    }
}
