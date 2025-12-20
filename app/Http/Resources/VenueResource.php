<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'address'     => $this->address,
            'city'        => $this->city,
            'street'      => $this->street,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'status'      => $this->status,
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),

            // Load relationships if eager loaded
            'spaces'    => SpaceResource::collection($this->whenLoaded('spaces')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),

            // Aggregate counts (from withCount)
            'spaces_count' => $this->when(isset($this->spaces_count), $this->spaces_count),
        ];
    }
}
