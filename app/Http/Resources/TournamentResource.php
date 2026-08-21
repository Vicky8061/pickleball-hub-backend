<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TournamentResource extends JsonResource
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

            // Owner
            'owner' => new UserResource(
                $this->whenLoaded('owner')
            ),

            // Court
            'court' => new CourtResource(
                $this->whenLoaded('court')
            ),

            // Tournament information
            'title' => $this->title,

            'description' => $this->description,

            // Tournament Banner
            'banner' => $this->banner
                ? asset('storage/' . $this->banner)
                : null,

            // Dates
            'tournament_date' => $this->tournament_date,

            'registration_last_date' => $this->registration_last_date,

            // Time
            'start_time' => $this->start_time,

            'end_time' => $this->end_time,

            // Tournament details
            'entry_fee' => $this->entry_fee,

            'max_participants' => $this->max_participants,

            'prize' => $this->prize,

            'status' => $this->status,

            // Participants
            'participants' => TournamentParticipantResource::collection(
                $this->whenLoaded('participants')
            ),

            // Timestamps
            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}
