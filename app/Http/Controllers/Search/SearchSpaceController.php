<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SearchSpaceController extends Controller
{
    /**
     * Search spaces with various filters including availability.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'city'          => 'nullable|string',
            'q'             => 'nullable|string',
            'space_type_id' => 'nullable|integer|exists:space_types,id',
            'min_price'     => 'nullable|numeric|min:0',
            'max_price'     => 'nullable|numeric|min:0',
            'start_time'    => 'nullable|date',
            'end_time'      => 'nullable|date|after:start_time',
            'per_page'      => 'nullable|integer|min:1|max:50',
        ]);

        $query = Space::query()->with(['venue', 'spaceType']);

        // Filter by city (from venue)
        if (!empty($data['city'])) {
            $query->whereHas('venue', function ($q) use ($data) {
                $q->where('city', 'LIKE', "%{$data['city']}%");
            });
        }

        // Search by keyword (space name, venue name, or address)
        if (!empty($data['q'])) {
            $query->where(function ($qBuilder) use ($data) {
                $qBuilder->where('name', 'LIKE', "%{$data['q']}%")
                    ->orWhereHas('venue', function ($q2) use ($data) {
                        $q2->where('name', 'LIKE', "%{$data['q']}%")
                           ->orWhere('address', 'LIKE', "%{$data['q']}%");
                    });
            });
        }

        // Filter by space type
        if (!empty($data['space_type_id'])) {
            $query->where('space_type_id', $data['space_type_id']);
        }

        // Filter by price range
        if (isset($data['min_price'])) {
            $query->where('price_per_hour', '>=', $data['min_price']);
        }

        if (isset($data['max_price'])) {
            $query->where('price_per_hour', '<=', $data['max_price']);
        }

        // Filter by availability (time range)
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $start = Carbon::parse($data['start_time']);
            $end = Carbon::parse($data['end_time']);

            $query->availableBetween($start, $end);
        }

        // Only show spaces from approved venues
        $query->whereHas('venue', function ($q) {
            $q->where('status', 'approved');
        });

        $perPage = min($data['per_page'] ?? 10, 50);
        $spaces = $query->paginate($perPage);

        return api_success([
            'items' => $spaces->items(),
            'meta'  => [
                'current_page' => $spaces->currentPage(),
                'last_page'    => $spaces->lastPage(),
                'total'        => $spaces->total(),
                'per_page'     => $spaces->perPage(),
            ],
        ]);
    }
}
