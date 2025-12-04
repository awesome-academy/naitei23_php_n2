<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Http\Resources\VenueResource;
use Illuminate\Http\Request;

class PublicVenueController extends Controller
{
    /**
     * Display the specified venue with spaces and amenities.
     */
    public function show(int $id)
    {
        $venue = Venue::query()
            ->with([
                'spaces',      // list spaces of this venue
                'amenities',   // venue amenities
            ])
            ->findOrFail($id);

        return api_success(new VenueResource($venue));
    }
}
