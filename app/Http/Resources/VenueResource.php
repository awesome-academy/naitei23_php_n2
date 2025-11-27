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

            // chỉ load nếu đã load quan hệ
            'amenities' => $this->whenLoaded('amenities'),
            'spaces'    => $this->whenLoaded('spaces'),
        ];
    }
}
