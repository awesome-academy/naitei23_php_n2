<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Space;
use Illuminate\Http\Request;

class OwnerSpaceController extends Controller
{
    /**
     * Display a listing of spaces for a venue.
     */
    public function index(Request $request, Venue $venue)
    {
        $this->authorize('view', $venue);

        $spaces = $venue->spaces()
            ->with(['spaceType', 'amenities'])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $spaces,
        ]);
    }

    /**
     * Store a newly created space.
     */
    public function store(Request $request, Venue $venue)
    {
        $this->authorize('create', [Space::class, $venue]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'space_type_id' => 'required|exists:space_types,id',
            'capacity' => 'required|integer|min:1',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'open_hour' => 'nullable|date_format:H:i',
            'close_hour' => 'nullable|date_format:H:i',
        ]);

        $data['venue_id'] = $venue->id;

        $space = Space::create($data);
        $space->load(['spaceType', 'amenities']);

        return response()->json([
            'success' => true,
            'message' => 'Space created successfully',
            'data' => $space,
        ], 201);
    }

    /**
     * Display the specified space.
     */
    public function show(Request $request, Space $space)
    {
        $this->authorize('view', $space);

        $space->load(['venue', 'spaceType', 'amenities']);

        return response()->json([
            'success' => true,
            'data' => $space,
        ]);
    }

    /**
     * Update the specified space.
     */
    public function update(Request $request, Space $space)
    {
        $this->authorize('update', $space);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'space_type_id' => 'sometimes|exists:space_types,id',
            'capacity' => 'sometimes|integer|min:1',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'open_hour' => 'nullable|date_format:H:i',
            'close_hour' => 'nullable|date_format:H:i',
        ]);

        $space->update($data);
        $space->load(['spaceType', 'amenities']);

        return response()->json([
            'success' => true,
            'message' => 'Space updated successfully',
            'data' => $space,
        ]);
    }

    /**
     * Remove the specified space.
     */
    public function destroy(Request $request, Space $space)
    {
        $this->authorize('delete', $space);

        $space->delete();

        return response()->json([
            'success' => true,
            'message' => 'Space deleted successfully',
        ]);
    }
}
