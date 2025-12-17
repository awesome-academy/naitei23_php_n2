<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Services\AdminVenueModerationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminVenueController extends Controller
{
    public function __construct(
        private AdminVenueModerationService $moderationService
    ) {}

    /**
     * List all venues with filters.
     *
     * GET /api/admin/venues
     *
     * Query params:
     * - status: pending|approved|rejected|blocked
     * - city: string
     * - q: search by name/address
     * - per_page: int
     */
    public function index(Request $request): JsonResponse
    {
        $query = Venue::query()->with('owner:id,full_name,email');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        // Search by name or address
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('address', 'like', "%{$searchTerm}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $venues = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    /**
     * Approve a pending venue.
     *
     * PATCH /api/admin/venues/{venue}/approve
     */
    public function approve(Venue $venue): JsonResponse
    {
        $venue = $this->moderationService->approve($venue);

        return response()->json([
            'success' => true,
            'message' => "Venue '{$venue->name}' has been approved.",
            'data' => $venue,
        ]);
    }

    /**
     * Reject a pending venue.
     *
     * PATCH /api/admin/venues/{venue}/reject
     *
     * Body:
     * - reason: string (optional)
     */
    public function reject(Request $request, Venue $venue): JsonResponse
    {
        $reason = $request->input('reason');
        $venue = $this->moderationService->reject($venue, $reason);

        return response()->json([
            'success' => true,
            'message' => "Venue '{$venue->name}' has been rejected.",
            'data' => $venue,
        ]);
    }

    /**
     * Block an approved venue.
     *
     * PATCH /api/admin/venues/{venue}/block
     *
     * Body:
     * - reason: string (optional)
     */
    public function block(Request $request, Venue $venue): JsonResponse
    {
        $reason = $request->input('reason');
        $venue = $this->moderationService->block($venue, $reason);

        return response()->json([
            'success' => true,
            'message' => "Venue '{$venue->name}' has been blocked.",
            'data' => $venue,
        ]);
    }

    /**
     * Unblock a blocked venue.
     *
     * PATCH /api/admin/venues/{venue}/unblock
     */
    public function unblock(Venue $venue): JsonResponse
    {
        $venue = $this->moderationService->unblock($venue);

        return response()->json([
            'success' => true,
            'message' => "Venue '{$venue->name}' has been unblocked.",
            'data' => $venue,
        ]);
    }
}
