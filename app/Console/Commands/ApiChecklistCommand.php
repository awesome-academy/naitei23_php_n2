<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Venue;
use App\Models\Space;
use App\Models\SpaceType;
use App\Models\Amenity;

class ApiChecklistCommand extends Command
{
    protected $signature = 'test:api-checklist';
    protected $description = 'Run API checklist tests for all owner endpoints';

    public function handle()
    {
        $base = 'http://127.0.0.1:8000/api';
        $results = [];

        $this->info("\n╔══════════════════════════════════════╗");
        $this->info("║     API CHECKLIST TEST               ║");
        $this->info("╚══════════════════════════════════════╝\n");

        // Create test owner
        $owner = User::create([
            'email' => 'checklist_' . time() . '@test.com',
            'full_name' => 'Checklist Owner',
            'password_hash' => bcrypt('password')
        ]);
        $token = $owner->createToken('checklist')->plainTextToken;

        // 1. VENUE CRUD
        $this->info("GROUP 1: VENUE CRUD");
        $results['1.1'] = $this->testEndpoint("1.1 GET /api/owner/venues",
            Http::withToken($token)->get("$base/owner/venues"));

        $r = Http::withToken($token)->post("$base/owner/venues", [
            'name' => 'Test Venue',
            'address' => '123 Test',
            'city' => 'Hanoi',
            'latitude' => 21.03,
            'longitude' => 105.85,
            'phone' => '0123456789'
        ]);
        $venueId = $r->json('data.id');
        $results['1.2'] = $this->testEndpoint("1.2 POST /api/owner/venues", $r, $venueId ? " - ID: $venueId" : "");

        if (!$venueId) {
            $this->error("❌ Cannot continue without venue");
            return 1;
        }

        $results['1.3'] = $this->testEndpoint("1.3 PUT /api/owner/venues/$venueId",
            Http::withToken($token)->put("$base/owner/venues/$venueId", ['name' => 'Updated Venue']));

        $results['1.4'] = $this->testEndpoint("1.4 GET /api/owner/venues/$venueId",
            Http::withToken($token)->get("$base/owner/venues/$venueId"));

        // 2. VENUE AMENITIES
        $this->info("\nGROUP 2: VENUE AMENITIES");
        $results['2.1'] = $this->testEndpoint("2.1 GET /api/owner/venues/$venueId/amenities",
            Http::withToken($token)->get("$base/owner/venues/$venueId/amenities"));

        $amenityIds = Amenity::limit(2)->pluck('id')->toArray();
        if (count($amenityIds) > 0) {
            $results['2.2'] = $this->testEndpoint("2.2 PUT /api/owner/venues/$venueId/amenities",
                Http::withToken($token)->put("$base/owner/venues/$venueId/amenities", ['amenity_ids' => $amenityIds]));
        } else {
            $this->warn("⏭️  2.2 PUT /api/owner/venues/$venueId/amenities - SKIPPED (no amenities)");
            $results['2.2'] = null;
        }

        // 3. SPACE CRUD
        $this->info("\nGROUP 3: SPACE CRUD");
        $results['3.1'] = $this->testEndpoint("3.1 GET /api/owner/venues/$venueId/spaces",
            Http::withToken($token)->get("$base/owner/venues/$venueId/spaces"));

        $spaceType = SpaceType::first();
        $spaceId = null;
        if ($spaceType) {
            $r = Http::withToken($token)->post("$base/owner/venues/$venueId/spaces", [
                'name' => 'Test Space',
                'space_type_id' => $spaceType->id,
                'capacity' => 10,
                'price_per_hour' => 100000,
                'open_time' => '08:00',
                'close_time' => '22:00'
            ]);
            $spaceId = $r->json('data.id');
            $results['3.2'] = $this->testEndpoint("3.2 POST /api/owner/venues/$venueId/spaces", $r, $spaceId ? " - ID: $spaceId" : "");
        } else {
            $this->warn("⏭️  3.2 POST /api/owner/venues/$venueId/spaces - SKIPPED (no space types)");
            $results['3.2'] = null;
        }

        if ($spaceId) {
            $results['3.3'] = $this->testEndpoint("3.3 GET /api/owner/spaces/$spaceId",
                Http::withToken($token)->get("$base/owner/spaces/$spaceId"));

            $results['3.4'] = $this->testEndpoint("3.4 PUT /api/owner/spaces/$spaceId",
                Http::withToken($token)->put("$base/owner/spaces/$spaceId", ['name' => 'Updated Space']));

            // 4. SPACE AMENITIES
            $this->info("\nGROUP 4: SPACE AMENITIES");
            $results['4.1'] = $this->testEndpoint("4.1 GET /api/owner/spaces/$spaceId/amenities",
                Http::withToken($token)->get("$base/owner/spaces/$spaceId/amenities"));

            if (count($amenityIds) > 0) {
                $results['4.2'] = $this->testEndpoint("4.2 PUT /api/owner/spaces/$spaceId/amenities",
                    Http::withToken($token)->put("$base/owner/spaces/$spaceId/amenities", ['amenity_ids' => $amenityIds]));
            } else {
                $this->warn("⏭️  4.2 PUT /api/owner/spaces/$spaceId/amenities - SKIPPED");
                $results['4.2'] = null;
            }

            // 6.2 Public space
            $results['6.2'] = $this->testEndpoint("6.2 GET /api/spaces/$spaceId (PUBLIC)",
                Http::get("$base/spaces/$spaceId"));
        }

        // 5. MANAGERS
        $this->info("\nGROUP 5: MANAGERS");
        $results['5.1'] = $this->testEndpoint("5.1 GET /api/owner/venues/$venueId/managers",
            Http::withToken($token)->get("$base/owner/venues/$venueId/managers"));

        $manager = User::create([
            'email' => 'manager_' . time() . '@test.com',
            'full_name' => 'Test Manager',
            'password_hash' => bcrypt('password')
        ]);
        $r = Http::withToken($token)->post("$base/owner/venues/$venueId/managers", ['email' => $manager->email]);
        $results['5.2'] = $this->testEndpoint("5.2 POST /api/owner/venues/$venueId/managers", $r);

        if ($r->successful()) {
            $results['5.3'] = $this->testEndpoint("5.3 DELETE /api/owner/venues/$venueId/managers/{$manager->id}",
                Http::withToken($token)->delete("$base/owner/venues/$venueId/managers/{$manager->id}"));
        }

        // 6.1 Public venue
        $this->info("\nGROUP 6: PUBLIC APIS");
        $results['6.1'] = $this->testEndpoint("6.1 GET /api/venues/$venueId (PUBLIC)",
            Http::get("$base/venues/$venueId"));

        // Cleanup
        $this->info("\n🧹 Cleaning up test data...");
        Venue::where('owner_id', $owner->id)->delete();
        $owner->tokens()->delete();
        $owner->delete();
        $manager?->delete();

        // Summary
        $this->info("\n╔══════════════════════════════════════╗");
        $this->info("║          SUMMARY                    ║");
        $this->info("╚══════════════════════════════════════╝\n");

        $total = count($results);
        $passed = count(array_filter($results, fn($r) => $r === true));
        $failed = count(array_filter($results, fn($r) => $r === false));
        $skipped = count(array_filter($results, fn($r) => $r === null));

        $this->info("Total: $total tests");
        $this->info("✅ Passed: $passed");
        $this->error("❌ Failed: $failed");
        $this->warn("⏭️  Skipped: $skipped");
        $this->info("\nSuccess Rate: " . round(($passed/$total)*100, 1) . "%");

        return $failed > 0 ? 1 : 0;
    }

    private function testEndpoint($label, $response, $extra = "")
    {
        $success = $response->successful();
        $status = $response->status();

        if ($success) {
            $this->info("✅ $label - Status $status$extra");
        } else {
            $this->error("❌ $label - Status $status");
            if ($response->json('message')) {
                $this->error("   Error: " . $response->json('message'));
            }
        }

        return $success;
    }
}
