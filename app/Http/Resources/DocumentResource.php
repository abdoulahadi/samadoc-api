<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request) {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'url' => asset('storage/' . $this->path),
            'level' => $this->level,
            'description' => $this->description,
            'folder' => $this->folder,
            'directory' => $this->directory->rep_name,
            'user' => $this->user->username,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
