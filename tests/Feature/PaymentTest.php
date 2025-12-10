<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Space;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Venue $venue;
    protected Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create();
        $this->owner = User::factory()->create();

        // Create venue and space
        $this->venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $this->space = Space::factory()->create([
            'venue_id' => $this->venue->id,
            'price_per_hour' => 100.00,
        ]);
    }

    /** @test */
    public function user_cannot_pay_for_pending_booking()
    {
        Sanctum::actingAs($this->user);

        // Create pending booking
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_PENDING_CONFIRMATION,
            'total_price' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Payment failed',
                'error' => 'Booking must be confirmed before payment',
            ]);

        $this->assertDatabaseMissing('payments', [
            'booking_id' => $booking->id,
        ]);
    }

    /** @test */
    public function user_can_pay_for_confirmed_booking()
    {
        Sanctum::actingAs($this->user);

        // Create confirmed booking
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_CONFIRMED,
            'total_price' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'credit_card',
            'meta' => ['card_last4' => '4242'],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Payment successful',
            ])
            ->assertJsonStructure([
                'payment' => [
                    'id',
                    'booking_id',
                    'amount',
                    'payment_method',
                    'transaction_id',
                    'transaction_status',
                    'paid_at',
                    'meta',
                    'booking' => [
                        'id',
                        'status',
                        'paid_at',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => '100.00',
            'payment_method' => 'credit_card',
            'transaction_status' => Payment::STATUS_SUCCESS,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_PAID,
        ]);

        $booking->refresh();
        $this->assertNotNull($booking->paid_at);
    }

    /** @test */
    public function user_cannot_pay_twice_for_same_booking()
    {
        Sanctum::actingAs($this->user);

        // Create confirmed booking
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_CONFIRMED,
            'total_price' => 100.00,
        ]);

        // First payment - should succeed
        $this->postJson('/api/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'credit_card',
        ])->assertStatus(201);

        // Second payment attempt - should fail
        $response = $this->postJson('/api/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'debit_card',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Payment failed',
                'error' => 'Booking already paid',
            ]);

        // Only one payment should exist
        $this->assertEquals(1, Payment::where('booking_id', $booking->id)->count());
    }

    /** @test */
    public function user_cannot_pay_for_other_users_booking()
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        // Create confirmed booking owned by $this->user
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_CONFIRMED,
            'total_price' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Payment failed',
                'error' => 'Unauthorized to pay for this booking',
            ]);

        $this->assertDatabaseMissing('payments', [
            'booking_id' => $booking->id,
        ]);
    }

    /** @test */
    public function user_can_list_their_payments()
    {
        Sanctum::actingAs($this->user);

        // Create multiple bookings and payments
        $booking1 = Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_PAID,
            'total_price' => 100.00,
        ]);

        $booking2 = Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_PAID,
            'total_price' => 200.00,
        ]);

        Payment::factory()->create([
            'booking_id' => $booking1->id,
            'amount' => 100.00,
        ]);

        Payment::factory()->create([
            'booking_id' => $booking2->id,
            'amount' => 200.00,
        ]);

        // Create payment for other user (should not be visible)
        $otherUser = User::factory()->create();
        $otherBooking = Booking::factory()->create([
            'user_id' => $otherUser->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_PAID,
        ]);
        Payment::factory()->create(['booking_id' => $otherBooking->id]);

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'payments')
            ->assertJsonStructure([
                'payments' => [
                    '*' => [
                        'id',
                        'booking_id',
                        'amount',
                        'payment_method',
                        'booking' => [
                            'id',
                            'space' => [
                                'id',
                                'venue',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function payment_requires_valid_booking_id()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/payments', [
            'booking_id' => 99999,
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);
    }

    /** @test */
    public function payment_requires_valid_payment_method()
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->space->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->postJson('/api/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'invalid_method',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_payment_endpoints()
    {
        $response = $this->postJson('/api/payments', [
            'booking_id' => 1,
            'payment_method' => 'credit_card',
        ]);
        $response->assertStatus(401);

        $response = $this->getJson('/api/payments');
        $response->assertStatus(401);
    }
}
