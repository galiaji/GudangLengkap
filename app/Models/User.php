<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Vinkla\Hashids\Facades\Hashids;

class User extends Authenticatable
{
    use Notifiable;

    // Tambahkan 'role' ke fillable agar bisa diisi mass assignment
    protected $fillable = [
        'name', 'username', 'email', 'password', 'role'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
}
