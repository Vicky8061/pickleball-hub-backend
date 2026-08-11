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
            'id'=>  $this->id,
            'booking_date'=>  $this->booking_date,
            'total_amount'=>  $this->total_amount,
            'payment_status'=>  $this->payment_status,
            'booking_status'=>  $this->booking_status,
            'user'=> new UserResource(
                $this->whenLoaded('user')
            ),
            'court'=> new CourtResource(
                $this->whenLoaded('court')
            ),
            'time_slot'=> new TimeSlotResource(
                $this->whenLoaded('timeSlot')
            ),
            'created_at'=>  $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
