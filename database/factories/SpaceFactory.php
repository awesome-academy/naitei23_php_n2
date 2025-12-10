<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => \App\Models\Venue::factory(),
            'space_type_id' => 1,
            'name' => fake()->words(3, true),
            'capacity' => fake()->numberBetween(1, 50),
            'price_per_hour' => fake()->randomFloat(2, 50, 500),
            'price_per_day' => fake()->randomFloat(2, 500, 5000),
            'price_per_month' => fake()->randomFloat(2, 5000, 50000),
            'open_hour' => '08:00:00',
            'close_hour' => '18:00:00',
        ];
    }
}
