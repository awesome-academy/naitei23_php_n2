<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Venue;
use App\Models\Space;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OwnerBookingController extends Controller
{
    /**
     * List all bookings for owner's venues/spaces with filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $q = Booking::with(['space.venue', 'user'])
            ->forOwner($user);

        // Filters
        if ($request->filled('status')) {
            $q->where('status', $request->query('status'));
        }
        if ($request->filled('venue_id')) {
            $q->whereHas('space', function ($q2) use ($request) {
                $q2->where('venue_id', $request->query('venue_id'));
            });
        }
        if ($request->filled('space_id')) {
            $q->where('space_id', $request->query('space_id'));
        }
        if ($request->filled('date_from')) {
            $q->where('start_time', '>=', Carbon::parse($request->query('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $q->where('end_time', '<=', Carbon::parse($request->query('date_to'))->endOfDay());
        }

        $bookings = $q->orderByDesc('start_time')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Get all bookings for a specific venue.
     */
    public function bookingsByVenue(Request $request, Venue $venue)
    {
        $user = $request->user();

        // Authorization: ensure user is owner or manager
        if (!($venue->owner_id === $user->id || $venue->managers()->where('user_id', $user->id)->exists())) {
            abort(403, 'Unauthorized to view bookings for this venue.');
        }

        $bookings = Booking::with(['space', 'user'])
            ->whereHas('space', function ($q) use ($venue) {
                $q->where('venue_id', $venue->id);
            })
            ->orderByDesc('start_time')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Get bookings for a specific space (calendar-friendly format).
     */
    public function bookingsBySpace(Request $request, Space $space)
    {
        $user = $request->user();
        $space->loadMissing('venue.managers');
        $venue = $space->venue;

        // Authorization
        if (!($venue->owner_id === $user->id || $venue->managers()->where('user_id', $user->id)->exists())) {
            abort(403, 'Unauthorized to view bookings for this space.');
        }

        $bookings = $space->bookings()
            ->select('id', 'start_time', 'end_time', 'status', 'user_id', 'total_price')
            ->with('user:id,full_name,email')
            ->orderBy('start_time')
            ->get();

        // Map into calendar-friendly format
        $events = $bookings->map(function ($b) {
            return [
                'id' => $b->id,
                'start' => $b->start_time->toIso8601String(),
                'end' => $b->end_time->toIso8601String(),
                'status' => $b->status,
                'total_price' => $b->total_price,
                'user' => [
                    'id' => $b->user->id,
                    'name' => $b->user->full_name,
                    'email' => $b->user->email,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Confirm a pending booking (atomic update to avoid race conditions).
     */
    public function confirm(Request $request, Booking $booking)
    {
        $user = $request->user();
        $this->authorize('manage', $booking);

        // Atomic update: only if status is still pending
        $updated = Booking::where('id', $booking->id)
            ->where('status', Booking::STATUS_PENDING_CONFIRMATION)
            ->update([
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be confirmed (status changed or conflict).'
            ], 409);
        }

        $booking->refresh();

        // Emit event for notifications
        event(new \App\Events\BookingConfirmed($booking));

        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'data' => $booking
        ]);
    }

    /**
     * Reject a pending booking (atomic update).
     */
    public function reject(Request $request, Booking $booking)
    {
        $user = $request->user();
        $this->authorize('manage', $booking);

        // Atomic update: only if status is still pending
        $updated = Booking::where('id', $booking->id)
            ->where('status', Booking::STATUS_PENDING_CONFIRMATION)
            ->update([
                'status' => Booking::STATUS_CANCELLED,
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be rejected (status changed).'
            ], 409);
        }

        $booking->refresh();

        // Emit event for notifications
        event(new \App\Events\BookingRejected($booking));

        return response()->json([
            'success' => true,
            'message' => 'Booking rejected successfully',
            'data' => $booking
        ]);
    }
}
