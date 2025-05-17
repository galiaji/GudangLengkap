<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Tambahkan 'username' ke fillable agar bisa diisi mass assignment
    protected $fillable = [
        'name', 'username', 'email', 'password'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
