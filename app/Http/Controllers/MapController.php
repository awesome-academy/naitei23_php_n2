<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    /**
     * Get all venues with coordinates for map markers
     * 
     * GET /api/map/venues
     * 
     * Query params:
     * - city (optional): Filter by city
     * - status (optional): Filter by status (default: approved)
     */
    public function venues(Request $request): JsonResponse
    {
        $query = Venue::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);

        // Filter by status (default: only approved venues)
        $status = $request->get('status', 'approved');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $venues = $query->select([
            'id',
            'name',
            'address',
            'city',
            'latitude',
            'longitude',
            'status'
        ])->get();

        // Transform for map markers
        $markers = $venues->map(function ($venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->name,
                'address' => $venue->address,
                'city' => $venue->city,
                'position' => [
                    'lat' => (float) $venue->latitude,
                    'lng' => (float) $venue->longitude,
                ],
                'coordinates' => [
                    'latitude' => (float) $venue->latitude,
                    'longitude' => (float) $venue->longitude,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'markers' => $markers,
                'total' => $markers->count(),
                'center' => $this->calculateCenter($markers),
            ],
        ]);
    }

    /**
     * Get venues within map bounds (viewport)
     * 
     * GET /api/map/venues/bounds
     * 
     * Query params:
     * - north (required): North latitude bound
     * - south (required): South latitude bound  
     * - east (required): East longitude bound
     * - west (required): West longitude bound
     */
    public function venuesByBounds(Request $request): JsonResponse
    {
        $request->validate([
            'north' => 'required|numeric|between:-90,90',
            'south' => 'required|numeric|between:-90,90',
            'east' => 'required|numeric|between:-180,180',
            'west' => 'required|numeric|between:-180,180',
        ]);

        $north = $request->north;
        $south = $request->south;
        $east = $request->east;
        $west = $request->west;

        $venues = Venue::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', 'approved')
            ->whereBetween('latitude', [$south, $north])
            ->whereBetween('longitude', [$west, $east])
            ->select([
                'id',
                'name', 
                'address',
                'city',
                'latitude',
                'longitude',
            ])
            ->limit(100) // Limit to prevent too many markers
            ->get();

        $markers = $venues->map(function ($venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->name,
                'address' => $venue->address,
                'city' => $venue->city,
                'position' => [
                    'lat' => (float) $venue->latitude,
                    'lng' => (float) $venue->longitude,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'markers' => $markers,
                'total' => $markers->count(),
                'bounds' => [
                    'north' => (float) $north,
                    'south' => (float) $south,
                    'east' => (float) $east,
                    'west' => (float) $west,
                ],
            ],
        ]);
    }

    /**
     * Get detailed venue info for map popup/info window
     * 
     * GET /api/map/venues/{id}
     */
    public function venueDetail(int $id): JsonResponse
    {
        $venue = Venue::with(['spaces' => function ($query) {
            $query->select('id', 'venue_id', 'name', 'price_per_hour', 'capacity')
                ->limit(5);
        }])
        ->findOrFail($id);

        // Calculate price range
        $prices = $venue->spaces->pluck('price_per_hour')->filter();
        $minPrice = $prices->min();
        $maxPrice = $prices->max();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $venue->id,
                'name' => $venue->name,
                'description' => $venue->description,
                'address' => $venue->address,
                'city' => $venue->city,
                'position' => [
                    'lat' => (float) $venue->latitude,
                    'lng' => (float) $venue->longitude,
                ],
                'spaces_count' => $venue->spaces->count(),
                'price_range' => [
                    'min' => $minPrice,
                    'max' => $maxPrice,
                    'formatted' => $minPrice && $maxPrice 
                        ? number_format($minPrice) . ' - ' . number_format($maxPrice) . ' VND/hour'
                        : null,
                ],
                'spaces_preview' => $venue->spaces->map(function ($space) {
                    return [
                        'id' => $space->id,
                        'name' => $space->name,
                        'price_per_hour' => $space->price_per_hour,
                        'capacity' => $space->capacity,
                    ];
                }),
                'detail_url' => "/api/venues/{$venue->id}",
            ],
        ]);
    }

    /**
     * Get map configuration (center, zoom, bounds for Vietnam)
     * 
     * GET /api/map/config
     */
    public function config(): JsonResponse
    {
        // Default center of Vietnam
        $defaultCenter = [
            'lat' => 16.0544,  // Da Nang - center of Vietnam
            'lng' => 107.5908,
        ];

        // Get actual center from venues if available
        $venues = Venue::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', 'approved')
            ->select('latitude', 'longitude')
            ->get();

        $center = $defaultCenter;
        if ($venues->count() > 0) {
            $center = [
                'lat' => $venues->avg('latitude'),
                'lng' => $venues->avg('longitude'),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'center' => $center,
                'zoom' => 6, // Default zoom level for Vietnam
                'minZoom' => 5,
                'maxZoom' => 18,
                'bounds' => [
                    // Vietnam bounds
                    'north' => 23.5,
                    'south' => 8.0,
                    'east' => 110.0,
                    'west' => 102.0,
                ],
                'cities' => [
                    [
                        'name' => 'Hanoi',
                        'position' => ['lat' => 21.0285, 'lng' => 105.8542],
                        'zoom' => 12,
                    ],
                    [
                        'name' => 'Ho Chi Minh',
                        'position' => ['lat' => 10.7769, 'lng' => 106.7009],
                        'zoom' => 12,
                    ],
                    [
                        'name' => 'Da Nang',
                        'position' => ['lat' => 16.0544, 'lng' => 108.2022],
                        'zoom' => 13,
                    ],
                    [
                        'name' => 'Hai Phong',
                        'position' => ['lat' => 20.8449, 'lng' => 106.6881],
                        'zoom' => 13,
                    ],
                    [
                        'name' => 'Can Tho',
                        'position' => ['lat' => 10.0452, 'lng' => 105.7469],
                        'zoom' => 13,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Search venues and return map data
     * 
     * GET /api/map/search
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters',
            ], 422);
        }

        $venues = Venue::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', 'approved')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%");
            })
            ->select(['id', 'name', 'address', 'city', 'latitude', 'longitude'])
            ->limit(20)
            ->get();

        $markers = $venues->map(function ($venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->name,
                'address' => $venue->address,
                'city' => $venue->city,
                'position' => [
                    'lat' => (float) $venue->latitude,
                    'lng' => (float) $venue->longitude,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'markers' => $markers,
                'total' => $markers->count(),
                'query' => $query,
                'center' => $this->calculateCenter($markers),
            ],
        ]);
    }

    /**
     * Calculate center point from markers
     */
    private function calculateCenter($markers): ?array
    {
        if ($markers->isEmpty()) {
            return null;
        }

        return [
            'lat' => $markers->avg('position.lat'),
            'lng' => $markers->avg('position.lng'),
        ];
    }
}
