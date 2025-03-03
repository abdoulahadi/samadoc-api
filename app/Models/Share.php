<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model {
    use HasFactory;

    protected $fillable = ['rep_id', 'owner_id', 'recipient_id', 'accepted', 'shared_at'];

    public function directory() {
        return $this->belongsTo(Directory::class, 'rep_id');
    }

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function recipient() {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
