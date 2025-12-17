<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Validation\ValidationException;

class AdminVenueModerationService
{
    /**
     * Approve a pending venue.
     */
    public function approve(Venue $venue): Venue
    {
        if ($venue->status !== Venue::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'venue' => 'Only pending venues can be approved.',
            ]);
        }

        $venue->status = Venue::STATUS_APPROVED;
        $venue->save();

        // Optional: Emit event
        // event(new VenueApproved($venue));

        return $venue;
    }

    /**
     * Reject a pending venue.
     */
    public function reject(Venue $venue, ?string $reason = null): Venue
    {
        if ($venue->status !== Venue::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'venue' => 'Only pending venues can be rejected.',
            ]);
        }

        $venue->status = 'rejected'; // Add to Venue constants if needed
        if ($reason) {
            $venue->rejection_reason = $reason;
        }
        $venue->save();

        // Optional: Emit event
        // event(new VenueRejected($venue, $reason));

        return $venue;
    }

    /**
     * Block an approved venue (for policy violations).
     */
    public function block(Venue $venue, ?string $reason = null): Venue
    {
        if ($venue->status !== Venue::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'venue' => 'Only approved venues can be blocked.',
            ]);
        }

        $venue->status = Venue::STATUS_BLOCKED;
        if ($reason) {
            $venue->block_reason = $reason;
        }
        $venue->save();

        // Optional: Emit event
        // event(new VenueBlocked($venue, $reason));

        return $venue;
    }

    /**
     * Unblock a blocked venue (restore to approved).
     */
    public function unblock(Venue $venue): Venue
    {
        if ($venue->status !== Venue::STATUS_BLOCKED) {
            throw ValidationException::withMessages([
                'venue' => 'Only blocked venues can be unblocked.',
            ]);
        }

        $venue->status = Venue::STATUS_APPROVED;
        $venue->block_reason = null;
        $venue->save();

        return $venue;
    }
}
