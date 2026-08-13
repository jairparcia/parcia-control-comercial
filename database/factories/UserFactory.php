<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'google_id'   => Str::uuid()->toString(),
            'name'        => fake()->name(),
            'email'       => fake()->unique()->safeEmail(),
            'avatar'      => null,
            'role'        => 'external',
            'webhook_url' => null,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn () => ['role' => 'internal']);
    }
}
