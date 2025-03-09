<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use GuzzleHttp\ClientTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username', 'email', 'password', 'occupation', 'university',
        'dbirth', 'pbirth', 'image', 'sex', 'state', 'activation_token', 'reset_token'
    ];

    protected $hidden = ['password', 'activation_token', 'reset_token', 'remember_token'];

    protected function password(): Attribute {
        return Attribute::make(set: fn($value) => bcrypt($value));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sharedDirectories()
    {
        return $this->belongsToMany(Directory::class, 'shares', 'recipient_id', 'rep_id')->withPivot('accepted', 'shared_at');
    }

    public function ownedDirectories()
    {
        return $this->belongsToMany(Directory::class, 'shares', 'owner_id', 'rep_id')->withPivot('accepted', 'shared_at');
    }

    public function sharesReceived()
    {
        return $this->hasMany(Share::class, 'recipient_id');
    }

    public function sharesGiven()
    {
        return $this->hasMany(Share::class, 'owner_id');
    }


}
