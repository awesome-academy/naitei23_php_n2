<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    /**
     * Determine if the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    /**
     * Determine if the user can manage (confirm/reject) the booking.
     * Owner of venue or assigned manager can manage.
     */
    public function manage(User $user, Booking $booking): bool
    {
        // Load relationships if not loaded
        $booking->loadMissing('space.venue.managers');

        // Owner of venue
        if ($booking->space && $booking->space->venue->owner_id === $user->id) {
            return true;
        }

        // Or manager assigned to venue
        return $booking->space->venue->managers()->where('user_id', $user->id)->exists();
    }
}
