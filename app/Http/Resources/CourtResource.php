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
            'owner'=>new UserResource(
                $this->whenLoaded('owner')
            ),

            'images'=> CourtImageResource::collection(
                $this->whenLoaded('images')
            ),
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
