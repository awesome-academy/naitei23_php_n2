<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminBookingResource;
use App\Models\Booking;
use App\Queries\AdminBookingQuery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminBookingController extends Controller
{
    public function __construct(
        private AdminBookingQuery $bookingQuery
    ) {}

    /**
     * List all bookings in system with filters.
     *
     * GET /api/admin/bookings
     *
     * Query params:
     * - status: string|array
     * - date_from: Y-m-d
     * - date_to: Y-m-d
     * - user_id: int
     * - space_id: int
     * - venue_id: int
     * - q: search term
     * - per_page: int (default 15, max 100)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status',
            'date_from',
            'date_to',
            'user_id',
            'space_id',
            'venue_id',
            'q',
        ]);

        $perPage = min((int) $request->get('per_page', 15), 100);

        $bookings = $this->bookingQuery
            ->apply($filters)
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AdminBookingResource::collection($bookings)->response()->getData(),
        ]);
    }

    /**
     * Show booking detail.
     *
     * GET /api/admin/bookings/{booking}
     */
    public function show(Booking $booking): JsonResponse
    {
        $booking->load(['user:id,full_name,email', 'space:id,venue_id,name', 'space.venue:id,name,status', 'payments']);

        return response()->json([
            'success' => true,
            'data' => new AdminBookingResource($booking),
        ]);
    }
}
