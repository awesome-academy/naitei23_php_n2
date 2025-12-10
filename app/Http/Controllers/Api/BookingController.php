<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Danh sách bookings của user hiện tại.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $bookings = Booking::with('space.venue')
            ->ownedBy($user->id)
            ->orderByDesc('start_time')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Chi tiết 1 booking.
     */
    public function show(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load('space.venue');

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Tạo booking mới.
     *
     * Optional query param: ?auto_confirm=true (for dev/testing only)
     */
    public function store(StoreBookingRequest $request)
    {
        $user = $request->user();

        // Allow auto-confirm for testing (check env or query param)
        $autoConfirm = $request->query('auto_confirm') === 'true'
            && config('app.env') !== 'production';

        $booking = $this->bookingService->create(
            $user,
            $request->validated(),
            $autoConfirm
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking
        ], 201);
    }

    /**
     * Hủy booking (chỉ pending mới được hủy).
     */
    public function destroy(Request $request, Booking $booking)
    {
        $this->authorize('cancel', $booking);

        $this->bookingService->cancel($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }
}
