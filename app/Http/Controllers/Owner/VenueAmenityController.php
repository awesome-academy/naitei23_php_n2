<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\SyncVenueAmenitiesRequest;
use App\Http\Resources\AmenityResource;
use App\Models\Venue;

class VenueAmenityController extends Controller
{
    /**
     * GET /api/owner/venues/{venue}/amenities
     * Trả về danh sách amenities đang gắn với venue.
     */
    public function index(Venue $venue)
    {
        $this->authorize('view', $venue);

        return AmenityResource::collection($venue->amenities);
    }

    /**
     * PUT /api/owner/venues/{venue}/amenities
     * Sync amenities cho venue (dùng sync()).
     */
    public function sync(SyncVenueAmenitiesRequest $request, Venue $venue)
    {
        $this->authorize('update', $venue);

        $amenityIds = $request->validated()['amenity_ids'] ?? [];

        $venue->amenities()->sync($amenityIds);

        // Trả danh sách mới sau sync
        return AmenityResource::collection($venue->amenities()->get());
    }
}
