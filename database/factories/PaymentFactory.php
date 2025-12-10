<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => \App\Models\Booking::factory(),
            'amount' => fake()->randomFloat(2, 100, 1000),
            'payment_method' => fake()->randomElement(['credit_card', 'debit_card', 'bank_transfer', 'e_wallet']),
            'transaction_id' => 'TXN_' . fake()->uuid(),
            'transaction_status' => \App\Models\Payment::STATUS_SUCCESS,
            'paid_at' => now(),
        ];
    }
}
