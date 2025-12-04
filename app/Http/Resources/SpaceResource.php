<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'venue_id'        => $this->venue_id,
            'space_type_id'   => $this->space_type_id,
            'name'            => $this->name,
            'capacity'        => $this->capacity,
            'price_per_hour'  => $this->price_per_hour,
            'price_per_day'   => $this->price_per_day,
            'price_per_month' => $this->price_per_month,
            'open_hour'       => $this->open_hour,
            'close_hour'      => $this->close_hour,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),

            // Load amenities nếu đã eager load
            'amenities' => AmenityResource::collection(
                $this->whenLoaded('amenities')
            ),
        ];
    }
}
