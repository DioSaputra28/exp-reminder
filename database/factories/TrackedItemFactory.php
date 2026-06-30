<?php

namespace Database\Factories;

use App\Enums\ReminderStatus;
use App\Models\Product;
use App\Models\TrackedItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<TrackedItem>
 */
class TrackedItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expiryDate = fake()->dateTimeBetween('+1 day', '+6 months');

        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'expiry_date' => $expiryDate,
            'remind_at' => null,
            'reminder_status' => ReminderStatus::Pending,
            'reminder_sent_at' => null,
        ];
    }

    /**
     * Set a reminder 7 days before expiry.
     */
    public function withReminder(int $daysBefore = 7): static
    {
        return $this->state(function (array $attributes) use ($daysBefore) {
            $expiryDate = Carbon::parse($attributes['expiry_date']);

            return [
                'remind_at' => $expiryDate->copy()->subDays($daysBefore),
            ];
        });
    }

    /**
     * Mark the item as already expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'remind_at' => null,
        ]);
    }

    /**
     * Mark the item as expiring soon (within 7 days).
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween('+1 day', '+7 days'),
        ]);
    }
}
