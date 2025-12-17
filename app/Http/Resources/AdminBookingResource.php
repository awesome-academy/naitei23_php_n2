<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBookingResource extends JsonResource
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
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_price' => (float) $this->total_price,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // User info
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'email' => $this->user->email,
            ],
            
            // Space info
            'space' => [
                'id' => $this->space->id,
                'name' => $this->space->name,
                'venue_id' => $this->space->venue_id,
            ],
            
            // Venue info (for easy filtering in FE)
            'venue' => [
                'id' => $this->space->venue->id,
                'name' => $this->space->venue->name,
                'status' => $this->space->venue->status,
            ],
        ];
    }
}
