<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Owner\StoreVenueRequest;
use App\Http\Requests\Owner\UpdateVenueRequest;
use App\Http\Resources\VenueResource;
use App\Models\Venue;

class VenueController extends Controller
{
    /**
     * GET /api/owner/venues
     * Trả về list venue của chủ nhà.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $venues = Venue::where('owner_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return VenueResource::collection($venues);
    }

    /**
     * POST /api/owner/venues
     * Tạo venue mới.
     */
    public function store(StoreVenueRequest $request)
    {
        $this->authorize('create', Venue::class);
        
        $user = $request->user();
        $data = $request->validated();

        // đảm bảo không bị FE phá owner_id / status
        unset($data['owner_id'], $data['status']);

        $data['owner_id'] = $user->id;
        $data['status']   = Venue::STATUS_PENDING;

        $venue = Venue::create($data);

        return (new VenueResource($venue))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/owner/venues/{venue}
     * Xem chi tiết venue.
     */
    public function show(Venue $venue)
    {
        $this->authorize('view', $venue);
        
        $venue->load(['amenities', 'spaces']);

        return new VenueResource($venue);
    }

    /**
     * PUT /api/owner/venues/{venue}
     * Update venue.
     */
    public function update(UpdateVenueRequest $request, Venue $venue)
    {
        $this->authorize('update', $venue);
        
        $data = $request->validated();

        // bảo vệ owner_id & status
        unset($data['owner_id'], $data['status']);

        $venue->update($data);

        return new VenueResource($venue);
    }

    /**
     * DELETE /api/owner/venues/{venue}
     * Xoá venue.
     */
    public function destroy(Venue $venue)
    {
        $this->authorize('delete', $venue);
        
        $venue->delete();

        return response()->json([
            'message' => 'Venue deleted successfully',
        ]);
    }
}
