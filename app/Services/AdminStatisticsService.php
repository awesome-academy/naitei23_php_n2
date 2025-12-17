<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminStatisticsService
{
    /**
     * Get dashboard statistics for a given month.
     * 
     * @param string|null $month Format: YYYY-MM
     * @return array
     */
    public function getStatistics(?string $month = null): array
    {
        // Default to current month if not provided
        if ($month) {
            try {
                $date = Carbon::createFromFormat('Y-m', $month);
            } catch (\Exception $e) {
                $date = Carbon::now();
            }
        } else {
            $date = Carbon::now();
        }

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        return [
            // User statistics
            'total_users' => User::count(),
            'new_users_in_month' => User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            
            // Booking statistics
            'total_bookings' => Booking::count(),
            'bookings_in_month' => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            
            // Bookings by status in month
            'bookings_by_status_in_month' => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            
            // Revenue statistics (only success payments)
            'revenue_in_month' => Payment::where('transaction_status', Payment::STATUS_SUCCESS)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            
            'total_revenue' => Payment::where('transaction_status', Payment::STATUS_SUCCESS)->sum('amount'),
            
            // Venue statistics
            'total_venues' => \App\Models\Venue::count(),
            'pending_venues' => \App\Models\Venue::where('status', 'pending')->count(),
            'approved_venues' => \App\Models\Venue::where('status', 'approved')->count(),
            
            // Additional metrics
            'month' => $date->format('Y-m'),
        ];
    }
}
