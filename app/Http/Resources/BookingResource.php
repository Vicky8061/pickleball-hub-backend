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
            'court_price' => $this->court_price ?? $this->total_amount,
            'platform_fee' => $this->platform_fee ?? 0.00,
            'admin_commission_rate' => $this->admin_commission_rate ?? 10.00,
            'admin_commission_amount' => $this->admin_commission_amount ?? round(($this->total_amount ?? 0) * 0.10, 2),
            'owner_payout_amount' => $this->owner_payout_amount ?? round(($this->total_amount ?? 0) * 0.90, 2),
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
