<?php

namespace App\Queries;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;

class AdminBookingQuery
{
    /**
     * Build query for admin booking list with filters.
     *
     * @param array $filters
     * @return Builder
     */
    public function apply(array $filters): Builder
    {
        $query = Booking::query()
            ->with(['user:id,full_name,email', 'space:id,venue_id,name', 'space.venue:id,name,status'])
            ->orderByDesc('start_time');

        // Filter by status (single or array)
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Filter by date range (based on start_time)
        if (!empty($filters['date_from'])) {
            $query->whereDate('start_time', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('start_time', '<=', $filters['date_to']);
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by space
        if (!empty($filters['space_id'])) {
            $query->where('space_id', $filters['space_id']);
        }

        // Filter by venue
        if (!empty($filters['venue_id'])) {
            $query->whereHas('space', function ($q) use ($filters) {
                $q->where('venue_id', $filters['venue_id']);
            });
        }

        // Search by user name/email or space name
        if (!empty($filters['q'])) {
            $searchTerm = $filters['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('full_name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                })
                ->orWhereHas('space', function ($spaceQuery) use ($searchTerm) {
                    $spaceQuery->where('name', 'like', "%{$searchTerm}%");
                });
            });
        }

        return $query;
    }
}
