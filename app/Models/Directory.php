<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    use HasFactory;

    protected $fillable = ['rep_name', 'level', 'description', 'shared', 'user_id'];

    public function sharedWithUsers()
{
    return $this->belongsToMany(User::class, 'shares', 'rep_id', 'recipient_id')->withPivot('accepted', 'shared_at');
}

public function owner()
{
    return $this->belongsToMany(User::class, 'shares', 'rep_id', 'owner_id')->withPivot('accepted', 'shared_at');
}

public function documents()
{
    return $this->hasMany(Document::class, 'rep_id');
}
}
