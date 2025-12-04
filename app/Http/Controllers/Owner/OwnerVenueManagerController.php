<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Http\Request;

class OwnerVenueManagerController extends Controller
{
    /**
     * Assign a manager to a venue.
     */
    public function store(Request $request, Venue $venue)
    {
        // Only venue owner can assign managers
        $this->authorize('update', $venue);

        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Ensure user has 'manager' role
        if (!$user->hasRole('manager')) {
            $user->assignRole('manager');
        }

        // Attach to venue_managers (avoid duplicate)
        $venue->managers()->syncWithoutDetaching([$user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Manager assigned to venue successfully',
            'data' => [
                'venue_id' => $venue->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
            ],
        ]);
    }

    /**
     * Remove a manager from a venue.
     */
    public function destroy(Request $request, Venue $venue, User $user)
    {
        $this->authorize('update', $venue);

        $venue->managers()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Manager removed from venue successfully',
        ]);
    }

    /**
     * List all managers of a venue.
     */
    public function index(Request $request, Venue $venue)
    {
        $this->authorize('view', $venue);

        $managers = $venue->managers()
            ->select(['users.id', 'users.full_name', 'users.email', 'users.phone_number'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $managers,
        ]);
    }
}
