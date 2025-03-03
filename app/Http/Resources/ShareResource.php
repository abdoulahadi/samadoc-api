<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request) {
        return [
            'id' => $this->id,
            'directory' => $this->directory->rep_name,
            'owner' => $this->owner->username,
            'recipient' => $this->recipient->username,
            'accepted' => $this->accepted,
            'shared_at' => $this->shared_at->toDateTimeString(),
        ];
    }

}
