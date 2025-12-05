<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Http\Resources\VenueNearbyResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RecommendationController extends Controller
{
    /**
     * Default radius in kilometers
     */
    private const DEFAULT_RADIUS_KM = 10;

    /**
     * Maximum radius allowed in kilometers
     */
    private const MAX_RADIUS_KM = 50;

    /**
     * Default number of results
     */
    private const DEFAULT_LIMIT = 10;

    /**
     * Maximum number of results
     */
    private const MAX_LIMIT = 50;

    /**
     * Get nearby venues based on user's geolocation
     * 
     * Uses Haversine formula to calculate distance between two points on Earth
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function nearbyVenues(Request $request): JsonResponse
    {
        try {
            // Validate request parameters
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:1|max:' . self::MAX_RADIUS_KM,
                'limit' => 'nullable|integer|min:1|max:' . self::MAX_LIMIT,
                'city' => 'nullable|string|max:100',
            ]);

            $userLat = $validated['latitude'];
            $userLng = $validated['longitude'];
            $radius = $validated['radius'] ?? self::DEFAULT_RADIUS_KM;
            $limit = $validated['limit'] ?? self::DEFAULT_LIMIT;
            $city = $validated['city'] ?? null;

            // Build query using Haversine formula
            // Formula: distance = 6371 * acos(cos(radians(lat1)) * cos(radians(lat2)) * cos(radians(lng2) - radians(lng1)) + sin(radians(lat1)) * sin(radians(lat2)))
            $venues = Venue::query()
                ->selectRaw("
                    venues.*,
                    (
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) 
                            + sin(radians(?)) * sin(radians(latitude))
                        )
                    ) AS distance
                ", [$userLat, $userLng, $userLat])
                ->where('status', Venue::STATUS_APPROVED)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->when($city, function ($query, $city) {
                    return $query->where('city', 'like', "%{$city}%");
                })
                ->having('distance', '<=', $radius)
                ->orderBy('distance', 'asc')
                ->limit($limit)
                ->with(['amenities', 'spaces' => function ($query) {
                    $query->select('id', 'venue_id', 'name', 'capacity', 'price_per_hour')
                          ->limit(5);
                }])
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Tìm thấy {$venues->count()} địa điểm trong bán kính {$radius}km.",
                'data' => [
                    'user_location' => [
                        'latitude' => $userLat,
                        'longitude' => $userLng,
                    ],
                    'search_params' => [
                        'radius_km' => $radius,
                        'limit' => $limit,
                        'city' => $city,
                    ],
                    'total_found' => $venues->count(),
                    'venues' => VenueNearbyResource::collection($venues),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tìm kiếm địa điểm.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get venues in a specific city with optional sorting by distance
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function venuesByCity(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'city' => 'required|string|max:100',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'limit' => 'nullable|integer|min:1|max:' . self::MAX_LIMIT,
                'page' => 'nullable|integer|min:1',
            ]);

            $city = $validated['city'];
            $userLat = $validated['latitude'] ?? null;
            $userLng = $validated['longitude'] ?? null;
            $limit = $validated['limit'] ?? self::DEFAULT_LIMIT;

            $query = Venue::query()
                ->where('status', Venue::STATUS_APPROVED)
                ->where('city', 'like', "%{$city}%");

            // If user location is provided, calculate distance and sort by it
            if ($userLat && $userLng) {
                $query->selectRaw("
                    venues.*,
                    (
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) 
                            + sin(radians(?)) * sin(radians(latitude))
                        )
                    ) AS distance
                ", [$userLat, $userLng, $userLat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('distance', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $venues = $query
                ->with(['amenities', 'spaces' => function ($query) {
                    $query->select('id', 'venue_id', 'name', 'capacity', 'price_per_hour')
                          ->limit(5);
                }])
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => "Tìm thấy {$venues->total()} địa điểm tại {$city}.",
                'data' => [
                    'city' => $city,
                    'user_location' => $userLat && $userLng ? [
                        'latitude' => $userLat,
                        'longitude' => $userLng,
                    ] : null,
                    'venues' => VenueNearbyResource::collection($venues->items()),
                    'pagination' => [
                        'current_page' => $venues->currentPage(),
                        'last_page' => $venues->lastPage(),
                        'per_page' => $venues->perPage(),
                        'total' => $venues->total(),
                    ],
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tìm kiếm địa điểm.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of available cities with venue count
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function availableCities(Request $request): JsonResponse
    {
        try {
            $cities = Venue::query()
                ->where('status', Venue::STATUS_APPROVED)
                ->selectRaw('city, COUNT(*) as venue_count')
                ->groupBy('city')
                ->orderBy('venue_count', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_cities' => $cities->count(),
                    'cities' => $cities->map(function ($item) {
                        return [
                            'city' => $item->city,
                            'venue_count' => $item->venue_count,
                        ];
                    }),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get popular/featured venues (can be based on various criteria)
     * Currently returns venues with most spaces
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function popularVenues(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'limit' => 'nullable|integer|min:1|max:20',
            ]);

            $userLat = $validated['latitude'] ?? null;
            $userLng = $validated['longitude'] ?? null;
            $limit = $validated['limit'] ?? 10;

            $query = Venue::query()
                ->where('status', Venue::STATUS_APPROVED)
                ->withCount('spaces')
                ->having('spaces_count', '>', 0);

            // If user location provided, also calculate distance
            if ($userLat && $userLng) {
                $query->selectRaw("
                    venues.*,
                    (
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) 
                            + sin(radians(?)) * sin(radians(latitude))
                        )
                    ) AS distance
                ", [$userLat, $userLng, $userLat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');
            }

            $venues = $query
                ->orderBy('spaces_count', 'desc')
                ->limit($limit)
                ->with(['amenities', 'spaces' => function ($query) {
                    $query->select('id', 'venue_id', 'name', 'capacity', 'price_per_hour')
                          ->limit(5);
                }])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_location' => $userLat && $userLng ? [
                        'latitude' => $userLat,
                        'longitude' => $userLng,
                    ] : null,
                    'total_found' => $venues->count(),
                    'venues' => VenueNearbyResource::collection($venues),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
