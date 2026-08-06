<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id'=> $this->id,
            'owner'=>new UserResource(
                $this->whenLoaded('owner')
            ),
            'court'=>new CourtResource(
                $this->whenLoaded('court')
            ),
            'title'=> $this->title,
            'description'=> $this->description,
            'banner'=> $this->banner,
            'tournament_date'=> $this->tournament_date,
            'regitration_last_date'=> $this->registration_last_date,
            'start_time'=> $this->start_time,
            'end_time'=> $this->end_time,
            'entry_fee'=> $this->entry_fee,
            'max_participants'=> $this->max_participants,
            'prize'=> $this->prize,
            'status'=> $this->status,

            'participants'=> TournamentParticipantResource::collection(
                $this->whenLoaded('participants')
            ),
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at,
        ];
    }
}
