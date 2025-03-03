<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    public function toArray(Request $request) {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'occupation' => $this->occupation,
            'university' => $this->university,
            'dbirth' => $this->dbirth,
            'pbirth' => $this->pbirth,
            'image' => $this->image,
            'sexe' => $this->sexe,
            'state' => $this->state,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}