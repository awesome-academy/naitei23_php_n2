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

class BookingCancelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $venue = Venue::factory()->create();
        $this->space = Space::factory()->create(['venue_id' => $venue->id]);
    }

    /** @test */
    public function user_can_cancel_pending_booking_without_payment()
    {
        $booking = Booking::factory()->create([
            'user_id'  => $this->user->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_PENDING_CONFIRMATION,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Booking cancelled successfully',
            ]);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
    }

    /** @test */
    public function user_can_cancel_confirmed_booking_without_payment()
    {
        $booking = Booking::factory()->create([
            'user_id'  => $this->user->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_CONFIRMED,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Booking cancelled successfully',
            ]);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
    }

    /** @test */
    public function user_cannot_cancel_paid_booking()
    {
        $booking = Booking::factory()->create([
            'user_id'  => $this->user->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_PAID,
        ]);

        // Create successful payment
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount'     => $booking->total_price,
            'transaction_status' => Payment::STATUS_SUCCESS,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking'])
            ->assertJsonFragment([
                'booking' => ['Paid booking cannot be cancelled.'],
            ]);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAID, $booking->status);
    }

    /** @test */
    public function user_cannot_cancel_booking_with_successful_payment_even_if_status_confirmed()
    {
        // Edge case: status is still confirmed but payment exists
        $booking = Booking::factory()->create([
            'user_id'  => $this->user->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_CONFIRMED,
        ]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount'     => $booking->total_price,
            'transaction_status' => Payment::STATUS_SUCCESS,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking']);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
    }

    /** @test */
    public function user_cannot_cancel_already_cancelled_booking()
    {
        $booking = Booking::factory()->create([
            'user_id'  => $this->user->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_CANCELLED,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking'])
            ->assertJson([
                'errors' => [
                    'booking' => ['Only pending or confirmed bookings can be cancelled.'],
                ],
            ]);
    }

    /** @test */
    public function user_cannot_cancel_booking_of_other_user()
    {
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id'  => $otherUser->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_PENDING_CONFIRMATION,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(403);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PENDING_CONFIRMATION, $booking->status);
    }

    /** @test */
    public function user_can_cancel_confirmed_booking_with_failed_payment()
    {
        // Payment failed, user should be able to cancel
        $booking = Booking::factory()->create([
            'user_id'  => $this->user->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_CONFIRMED,
        ]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount'     => $booking->total_price,
            'transaction_status' => Payment::STATUS_FAILED,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
    }

    /** @test */
    public function unauthenticated_user_cannot_cancel_booking()
    {
        $booking = Booking::factory()->create([
            'user_id'  => $this->user->id,
            'space_id' => $this->space->id,
            'status'   => Booking::STATUS_PENDING_CONFIRMATION,
        ]);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(401);
    }
}
