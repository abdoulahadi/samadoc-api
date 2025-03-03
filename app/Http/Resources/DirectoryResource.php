<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectoryResource extends JsonResource {
    public function toArray(Request $request) {
        return [
            'id' => $this->id,
            'rep_name' => $this->rep_name,
            'level' => $this->level,
            'description' => $this->description,
            'shared' => $this->shared,
            'user' => $this->user->username,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
