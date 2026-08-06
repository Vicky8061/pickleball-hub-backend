<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentParticipantResource extends JsonResource
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
            'user'=> new UserResource(
                $this->whenLoaded('user')
            ),
            'payment_status'=> $this->payment_status,
            'created_at'=> $this->created_at,
        ];
    }
}
