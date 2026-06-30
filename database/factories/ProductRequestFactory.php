<?php

namespace Database\Factories;

use App\Enums\ProductRequestStatus;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductRequest>
 */
class ProductRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'barcode' => fake()->unique()->ean13(),
            'description' => fake()->optional()->sentence(),
            'status' => ProductRequestStatus::Pending,
            'rejection_reason' => null,
        ];
    }

    /**
     * Mark the request as approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductRequestStatus::Approved,
        ]);
    }

    /**
     * Mark the request as rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductRequestStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
