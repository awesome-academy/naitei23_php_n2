<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'space_id' => \App\Models\Space::factory(),
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'total_price' => fake()->randomFloat(2, 100, 1000),
            'status' => \App\Models\Booking::STATUS_PENDING_CONFIRMATION,
        ];
    }
}
