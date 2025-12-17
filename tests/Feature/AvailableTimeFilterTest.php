<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailableTimeFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Venue $venue;
    protected Space $spaceA;
    protected Space $spaceB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->venue = Venue::factory()->create(['status' => 'approved']);

        $this->spaceA = Space::factory()->create([
            'venue_id' => $this->venue->id,
            'name' => 'Space A',
            'price_per_hour' => 100,
        ]);

        $this->spaceB = Space::factory()->create([
            'venue_id' => $this->venue->id,
            'name' => 'Space B',
            'price_per_hour' => 150,
        ]);
    }

    /** @test */
    public function search_returns_all_spaces_without_time_filter()
    {
        $response = $this->getJson('/api/search/spaces');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.items');

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();
        $this->assertContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_excludes_space_with_confirmed_booking_in_time_range()
    {
        // Space A has confirmed booking: 09:00-11:00
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 09:00:00',
            'end_time' => '2025-12-15 11:00:00',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Search for 10:00-12:00 (overlaps with booking)
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 10:00:00',
            'end_time' => '2025-12-15 12:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        // Space A should NOT be in results (has overlapping booking)
        $this->assertNotContains('Space A', $spaceNames);

        // Space B should be available
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_includes_space_when_no_overlap()
    {
        // Space A has confirmed booking: 09:00-11:00
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 09:00:00',
            'end_time' => '2025-12-15 11:00:00',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Search for 11:00-13:00 (exactly after booking ends)
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 11:00:00',
            'end_time' => '2025-12-15 13:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        // Both spaces should be available (no overlap)
        $this->assertContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_includes_space_when_search_ends_exactly_when_booking_starts()
    {
        // Space A has booking: 09:00-11:00
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 09:00:00',
            'end_time' => '2025-12-15 11:00:00',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Search 08:00-09:00 (ends exactly when booking starts)
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 08:00:00',
            'end_time' => '2025-12-15 09:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        // Space A SHOULD be available (no overlap - touching boundary is OK)
        $this->assertContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_excludes_space_with_paid_booking()
    {
        // Space A has paid booking
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 14:00:00',
            'end_time' => '2025-12-15 16:00:00',
            'status' => Booking::STATUS_PAID,
        ]);

        // Search overlapping time
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 15:00:00',
            'end_time' => '2025-12-15 17:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        $this->assertNotContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_includes_space_with_pending_booking()
    {
        // Space A has PENDING booking (not confirmed yet)
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 10:00:00',
            'end_time' => '2025-12-15 12:00:00',
            'status' => Booking::STATUS_PENDING_CONFIRMATION,
        ]);

        // Search overlapping time
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 11:00:00',
            'end_time' => '2025-12-15 13:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        // Space A SHOULD be included (pending bookings don't block availability)
        $this->assertContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_includes_space_with_cancelled_booking()
    {
        // Space A has cancelled booking
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 09:00:00',
            'end_time' => '2025-12-15 11:00:00',
            'status' => Booking::STATUS_CANCELLED,
        ]);

        // Search overlapping time
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 10:00:00',
            'end_time' => '2025-12-15 12:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        // Both spaces available (cancelled bookings don't block)
        $this->assertContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_handles_booking_that_fully_contains_search_range()
    {
        // Space A: booking 08:00-18:00 (covers entire day)
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 08:00:00',
            'end_time' => '2025-12-15 18:00:00',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Search 10:00-12:00 (inside the booking)
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 10:00:00',
            'end_time' => '2025-12-15 12:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        $this->assertNotContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_handles_multiple_bookings_on_same_space()
    {
        // Space A: two confirmed bookings
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 09:00:00',
            'end_time' => '2025-12-15 11:00:00',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 14:00:00',
            'end_time' => '2025-12-15 16:00:00',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Search 12:00-13:00 (between bookings)
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 12:00:00',
            'end_time' => '2025-12-15 13:00:00',
        ]));

        $response->assertStatus(200);

        $spaceNames = collect($response->json('data.items'))->pluck('name')->toArray();

        // Space A should be available (no overlap)
        $this->assertContains('Space A', $spaceNames);
        $this->assertContains('Space B', $spaceNames);
    }

    /** @test */
    public function search_with_invalid_time_range_returns_validation_error()
    {
        // end_time before start_time
        $response = $this->getJson('/api/search/spaces?' . http_build_query([
            'start_time' => '2025-12-15 14:00:00',
            'end_time' => '2025-12-15 10:00:00',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    /** @test */
    public function scope_available_between_works_directly_on_model()
    {
        // Test the scope directly
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'space_id' => $this->spaceA->id,
            'start_time' => '2025-12-15 10:00:00',
            'end_time' => '2025-12-15 12:00:00',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $start = Carbon::parse('2025-12-15 11:00:00');
        $end = Carbon::parse('2025-12-15 13:00:00');

        $availableSpaces = Space::availableBetween($start, $end)->get();

        // Space A should NOT be in results
        $this->assertFalse($availableSpaces->contains('id', $this->spaceA->id));

        // Space B should be available
        $this->assertTrue($availableSpaces->contains('id', $this->spaceB->id));
    }
}
