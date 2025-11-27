<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\SyncSpaceAmenitiesRequest;
use App\Http\Resources\AmenityResource;
use App\Models\Space;

class SpaceAmenityController extends Controller
{
    /**
     * List amenities of a space (owner only)
     */
    public function index(Space $space)
    {
        // Check permission through venue: only venue owner can view
        $this->authorize('view', $space->venue);

        $space->load('amenities');

        return AmenityResource::collection($space->amenities);
    }

    /**
     * Sync amenities list for space
     */
    public function sync(SyncSpaceAmenitiesRequest $request, Space $space)
    {
        $this->authorize('update', $space->venue);

        $data = $request->validated();
        $ids  = $data['amenity_ids'] ?? [];

        $space->amenities()->sync($ids);

        $space->load('amenities');

        return AmenityResource::collection($space->amenities);
    }
}
