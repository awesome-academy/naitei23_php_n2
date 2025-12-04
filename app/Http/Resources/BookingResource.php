<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'status'      => $this->status,
            'total_price' => $this->total_price,
            'payment'     => new PaymentResource($this->whenLoaded('payment')),
        ];
    }
}
