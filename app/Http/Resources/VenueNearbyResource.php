<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueNearbyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'street' => $this->street,
            'location' => [
                'latitude' => $this->latitude ? (float) $this->latitude : null,
                'longitude' => $this->longitude ? (float) $this->longitude : null,
            ],
            // Distance in km (if calculated)
            'distance_km' => $this->distance !== null ? round($this->distance, 2) : null,
            'distance_text' => $this->formatDistance($this->distance),
            
            // Amenities
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            
            // Spaces summary
            'spaces_count' => $this->whenCounted('spaces'),
            'spaces_preview' => $this->when(
                $this->relationLoaded('spaces'),
                fn () => $this->spaces->map(fn ($space) => [
                    'id' => $space->id,
                    'name' => $space->name,
                    'capacity' => $space->capacity,
                    'price_per_hour' => $space->price_per_hour,
                ])
            ),
            
            // Price range (from spaces)
            'price_range' => $this->when(
                $this->relationLoaded('spaces') && $this->spaces->count() > 0,
                fn () => [
                    'min_price_per_hour' => $this->spaces->min('price_per_hour'),
                    'max_price_per_hour' => $this->spaces->max('price_per_hour'),
                    'currency' => 'VND',
                ]
            ),
            
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Format distance to human-readable text
     */
    private function formatDistance(?float $distanceKm): ?string
    {
        if ($distanceKm === null) {
            return null;
        }

        if ($distanceKm < 1) {
            // Less than 1km, show in meters
            $meters = round($distanceKm * 1000);
            return "{$meters}m";
        }

        return round($distanceKm, 1) . "km";
    }
}
