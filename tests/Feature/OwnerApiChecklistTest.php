<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Venue;
use App\Models\Space;
use App\Models\Amenity;
use App\Models\SpaceType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OwnerApiChecklistTest extends TestCase
{
    protected $owner;
    protected $token;
    protected $venue;
    protected $space;

    protected function setUp(): void
    {
        parent::setUp();

        // Use existing database (already has migrate:fresh --seed)
        $this->owner = User::firstOrCreate(
            ['email' => 'test_owner_' . time() . '@test.com'],
            ['full_name' => 'Test Owner', 'password_hash' => bcrypt('password')]
        );

        $this->token = $this->owner->createToken('test')->plainTextToken;
    }

    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->owner) {
            Venue::where('owner_id', $this->owner->id)->delete();
            $this->owner->tokens()->delete();
            $this->owner->delete();
        }

        parent::tearDown();
    }    /** @test */
    public function test_1_1_owner_can_list_venues()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/owner/venues');

        $response->assertStatus(200);
        $this->assertTrue(isset($response['data']));
        echo "\n✅ 1.1 GET /api/owner/venues - Status " . $response->status();
    }

    /** @test */
    public function test_1_2_owner_can_create_venue()
    {
        $data = [
            'name' => 'AL Coworking Space',
            'address' => '123 Test Street',
            'city' => 'Hanoi',
            'latitude' => 21.03,
            'longitude' => 105.85,
            'description' => 'Test venue for API',
            'phone' => '0123456789'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/owner/venues', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('venues', ['name' => 'AL Coworking Space', 'owner_id' => $this->owner->id]);
        $this->venue = Venue::where('name', 'AL Coworking Space')->first();
        echo "\n✅ 1.2 POST /api/owner/venues - Status " . $response->status() . " - Venue ID: " . $this->venue->id;
    }

    /** @test */
    public function test_1_3_owner_can_update_venue()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/owner/venues/{$venue->id}", [
            'name' => 'AL Coworking Space - Updated'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('venues', ['id' => $venue->id, 'name' => 'AL Coworking Space - Updated']);
        echo "\n✅ 1.3 PUT /api/owner/venues/{id} - Status " . $response->status();
    }

    /** @test */
    public function test_1_4_owner_can_show_venue()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/owner/venues/{$venue->id}");

        $response->assertStatus(200);
        $this->assertTrue(isset($response['data']));
        echo "\n✅ 1.4 GET /api/owner/venues/{id} - Status " . $response->status();
    }

    /** @test */
    public function test_1_5_owner_can_delete_venue()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/owner/venues/{$venue->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('venues', ['id' => $venue->id, 'deleted_at' => null]);
        echo "\n✅ 1.5 DELETE /api/owner/venues/{id} - Status " . $response->status();
    }

    /** @test */
    public function test_2_1_owner_can_get_venue_amenities()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/owner/venues/{$venue->id}/amenities");

        $response->assertStatus(200);
        echo "\n✅ 2.1 GET /api/owner/venues/{id}/amenities - Status " . $response->status();
    }

    /** @test */
    public function test_2_2_owner_can_update_venue_amenities()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $amenities = Amenity::limit(2)->pluck('id')->toArray();

        if (count($amenities) > 0) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->putJson("/api/owner/venues/{$venue->id}/amenities", [
                'amenity_ids' => $amenities
            ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('venue_amenities', ['venue_id' => $venue->id, 'amenity_id' => $amenities[0]]);
            echo "\n✅ 2.2 PUT /api/owner/venues/{id}/amenities - Status " . $response->status();
        } else {
            echo "\n⏭️  2.2 PUT /api/owner/venues/{id}/amenities - SKIPPED (no amenities)";
        }
    }

    /** @test */
    public function test_3_1_owner_can_list_spaces()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/owner/venues/{$venue->id}/spaces");

        $response->assertStatus(200);
        echo "\n✅ 3.1 GET /api/owner/venues/{id}/spaces - Status " . $response->status();
    }

    /** @test */
    public function test_3_2_owner_can_create_space()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $spaceType = SpaceType::first();

        if ($spaceType) {
            $data = [
                'name' => 'Meeting Room 1',
                'space_type_id' => $spaceType->id,
                'capacity' => 10,
                'price_per_hour' => 100000,
                'open_time' => '08:00',
                'close_time' => '22:00'
            ];

            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->postJson("/api/owner/venues/{$venue->id}/spaces", $data);

            $response->assertStatus(201);
            $this->assertDatabaseHas('spaces', ['name' => 'Meeting Room 1', 'venue_id' => $venue->id]);
            echo "\n✅ 3.2 POST /api/owner/venues/{id}/spaces - Status " . $response->status();
        } else {
            echo "\n⏭️  3.2 POST /api/owner/venues/{id}/spaces - SKIPPED (no space types)";
        }
    }

    /** @test */
    public function test_3_3_owner_can_show_space()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $space = Space::factory()->create(['venue_id' => $venue->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/owner/spaces/{$space->id}");

        $response->assertStatus(200);
        echo "\n✅ 3.3 GET /api/owner/spaces/{id} - Status " . $response->status();
    }

    /** @test */
    public function test_3_4_owner_can_update_space()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $space = Space::factory()->create(['venue_id' => $venue->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/owner/spaces/{$space->id}", [
            'name' => 'Meeting Room 1 - Updated'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('spaces', ['id' => $space->id, 'name' => 'Meeting Room 1 - Updated']);
        echo "\n✅ 3.4 PUT /api/owner/spaces/{id} - Status " . $response->status();
    }

    /** @test */
    public function test_3_5_owner_can_delete_space()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $space = Space::factory()->create(['venue_id' => $venue->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/owner/spaces/{$space->id}");

        $response->assertStatus(200);
        echo "\n✅ 3.5 DELETE /api/owner/spaces/{id} - Status " . $response->status();
    }

    /** @test */
    public function test_4_1_owner_can_get_space_amenities()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $space = Space::factory()->create(['venue_id' => $venue->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/owner/spaces/{$space->id}/amenities");

        $response->assertStatus(200);
        echo "\n✅ 4.1 GET /api/owner/spaces/{id}/amenities - Status " . $response->status();
    }

    /** @test */
    public function test_4_2_owner_can_update_space_amenities()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $space = Space::factory()->create(['venue_id' => $venue->id]);
        $amenities = Amenity::limit(2)->pluck('id')->toArray();

        if (count($amenities) > 0) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->putJson("/api/owner/spaces/{$space->id}/amenities", [
                'amenity_ids' => $amenities
            ]);

            $response->assertStatus(200);
            echo "\n✅ 4.2 PUT /api/owner/spaces/{id}/amenities - Status " . $response->status();
        } else {
            echo "\n⏭️  4.2 PUT /api/owner/spaces/{id}/amenities - SKIPPED (no amenities)";
        }
    }

    /** @test */
    public function test_5_1_owner_can_list_managers()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/owner/venues/{$venue->id}/managers");

        $response->assertStatus(200);
        echo "\n✅ 5.1 GET /api/owner/venues/{id}/managers - Status " . $response->status();
    }

    /** @test */
    public function test_5_2_owner_can_add_manager()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $manager = User::factory()->create(['email' => 'manager@test.com']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson("/api/owner/venues/{$venue->id}/managers", [
            'email' => 'manager@test.com'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('venue_managers', ['venue_id' => $venue->id, 'user_id' => $manager->id]);
        echo "\n✅ 5.2 POST /api/owner/venues/{id}/managers - Status " . $response->status();
    }

    /** @test */
    public function test_5_3_owner_can_remove_manager()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $manager = User::factory()->create(['email' => 'manager2@test.com']);
        $venue->managers()->attach($manager->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/owner/venues/{$venue->id}/managers/{$manager->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('venue_managers', ['venue_id' => $venue->id, 'user_id' => $manager->id]);
        echo "\n✅ 5.3 DELETE /api/owner/venues/{id}/managers/{user} - Status " . $response->status();
    }

    /** @test */
    public function test_6_1_public_can_view_venue_detail()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->getJson("/api/venues/{$venue->id}");

        $response->assertStatus(200);
        $this->assertTrue(isset($response['data']));
        echo "\n✅ 6.1 GET /api/venues/{id} (PUBLIC) - Status " . $response->status();
    }

    /** @test */
    public function test_6_2_public_can_view_space_detail()
    {
        $venue = Venue::factory()->create(['owner_id' => $this->owner->id]);
        $space = Space::factory()->create(['venue_id' => $venue->id]);

        $response = $this->getJson("/api/spaces/{$space->id}");

        $response->assertStatus(200);
        $this->assertTrue(isset($response['data']));
        echo "\n✅ 6.2 GET /api/spaces/{id} (PUBLIC) - Status " . $response->status();
    }
}
