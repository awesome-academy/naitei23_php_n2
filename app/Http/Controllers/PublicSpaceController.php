<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Http\Resources\SpaceResource;
use Illuminate\Http\Request;

class PublicSpaceController extends Controller
{
    /**
     * Display the specified space with venue, amenities, and upcoming bookings.
     */
    public function show(int $id)
    {
        $space = Space::query()
            ->with([
                'venue',                    // venue containing this space
                'amenities',                // space amenities
                'upcomingBookings.payment', // upcoming bookings (30 days) with payment info
            ])
            ->findOrFail($id);

        return api_success(new SpaceResource($space));
    }
}
