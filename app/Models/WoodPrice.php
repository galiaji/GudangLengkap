<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoodPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'wood_type_id',
        'grade_id',
        'panjang',
        'price_per_m3',
    ];
}
