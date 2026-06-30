<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'password' => null,
            'google_id' => fake()->unique()->numerify('####################'),
            'avatar' => fake()->imageUrl(96, 96, 'people'),
            'role' => UserRole::User,
            'is_active' => true,
            'telegram_user_id' => null,
            'last_login_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'email_verified_at' => now(),
            'remember_token' => null,
        ];
    }

    /**
     * Indicate that the user is an admin with email/password auth.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
            'google_id' => null,
            'avatar' => null,
            'password' => 'password',
        ]);
    }

    /**
     * Indicate that the user has a linked Telegram account.
     */
    public function withTelegram(): static
    {
        return $this->state(fn (array $attributes) => [
            'telegram_user_id' => fake()->numerify('#########'),
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
