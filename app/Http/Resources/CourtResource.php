<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avgRating = $this->reviews_avg_rating !== null
            ? (float) $this->reviews_avg_rating
            : ($this->relationLoaded('reviews') ? (float) ($this->reviews->avg('rating') ?? 0) : (float) ($this->reviews()->avg('rating') ?? 0));

        $reviewsCount = $this->reviews_count !== null
            ? (int) $this->reviews_count
            : ($this->relationLoaded('reviews') ? $this->reviews->count() : $this->reviews()->count());

        return [
            'id'=> $this->id,
            'name'=> $this->name,
            'description'=> $this->description,
            'address'=>$this->address,
            'latitude'=>$this->latitude,
            'longitude'=>$this->longitude,
            'price_per_hour'=> $this->price_per_hour,
            'court_type'=>$this->court_type,
            'opening_time'=>$this->opening_time,
            'closing_time'=> $this->closing_time,
            'status'=>$this->status,
            'average_rating' => round($avgRating, 1),
            'reviews_count' => $reviewsCount,
            'cover_image_url' => $this->relationLoaded('images') && $this->images->count() > 0
                ? (optional($this->images->firstWhere('is_primary', true))->image_url ?? optional($this->images->first())->image_url)
                : null,
            'owner'=>new UserResource(
                $this->whenLoaded('owner')
            ),

            'images'=> CourtImageResource::collection(
                $this->whenLoaded('images')
            ),
            'time_slots'=> TimeSlotResource::collection(
                $this->whenLoaded('timeSlots')
            ),
            'reviews'=> ReviewResource::collection(
                $this->whenLoaded('reviews')
            ),
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
