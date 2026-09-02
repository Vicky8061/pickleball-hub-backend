<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerApplicationResource extends JsonResource
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

            'user' => new UserResource($this->whenLoaded('user')),

            'business_name' => $this->business_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,

            'experience' => $this->experience,
            'description' => $this->description,

            'document' => $this->document,
            'document_url' => $this->document ? (str_starts_with($this->document, 'http') ? $this->document : asset('storage/' . $this->document)) : null,

            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'reviewed_at' => $this->reviewed_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}