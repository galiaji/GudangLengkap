<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoodPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'wood_type',      
        'grade',          
        'panjang',
        'harga_beli_per_m3',
        'harga_jual_per_m3'
    ];
}
