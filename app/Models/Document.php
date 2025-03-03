<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'path', 'level', 'description', 'folder', 'rep_id', 'user_id'];

    public function directory() {
        return $this->belongsTo(Directory::class, 'rep_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
