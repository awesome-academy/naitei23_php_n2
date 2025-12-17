<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AdminPaymentController extends Controller
{
    /**
     * List all payments with filters.
     * 
     * GET /api/admin/payments
     * 
     * Query params:
     * - status: success|failed|pending
     * - method: payment method
     * - month: YYYY-MM
     * - venue_id: int
     * - space_id: int
     * - user_id: int
     * - per_page: int
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query()
            ->with([
                'booking:id,user_id,space_id,start_time,end_time,total_price',
                'booking.user:id,full_name,email',
                'booking.space:id,venue_id,name',
                'booking.space.venue:id,name'
            ])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('transaction_status')) {
            $query->where('transaction_status', $request->transaction_status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by month (YYYY-MM)
        if ($request->filled('month')) {
            try {
                $month = Carbon::createFromFormat('Y-m', $request->month);
                $query->whereYear('created_at', $month->year)
                      ->whereMonth('created_at', $month->month);
            } catch (\Exception $e) {
                // Invalid month format, skip filter
            }
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->whereHas('booking', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        // Filter by space
        if ($request->filled('space_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('booking', function ($bookingQuery) use ($request) {
                    $bookingQuery->where('space_id', $request->space_id);
                });
            });
        }

        // Filter by venue
        if ($request->filled('venue_id')) {
            $query->whereHas('booking.space', function ($q) use ($request) {
                $q->where('venue_id', $request->venue_id);
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }
}
