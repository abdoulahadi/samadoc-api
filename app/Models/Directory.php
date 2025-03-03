<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    use HasFactory;

    protected $fillable = ['rep_name', 'level', 'description', 'shared', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
