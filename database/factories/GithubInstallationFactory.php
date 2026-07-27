<?php

namespace Database\Factories;

use App\Models\GithubInstallation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GithubInstallation>
 */
class GithubInstallationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'installation_id' => fake()->unique()->numberBetween(1, 999999),
            'account_login' => fake()->userName(),
            'account_type' => fake()->randomElement(['User', 'Organization']),
            'suspended_at' => null,
            'uninstalled_at' => null,
        ];
    }
}
