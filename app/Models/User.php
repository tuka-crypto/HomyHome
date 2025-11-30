<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = [
        'mobile_phone',
        'password',
        'first_name',
        'last_name',
        'role',
        'date_of_birth',
        'profile_image',
        'id_card_image',
        'is_approved',
    ];
    public function isOwner():bool
    {
        return $this->role === 'owner';
    }
    public function isTenant():bool
    {
        return $this->role === 'tenant';
    }
    public function isAdmin():bool
    {
        return $this->role === 'admin';
    }
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}