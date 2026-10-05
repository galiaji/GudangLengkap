<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoodStock extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'wood_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'wood_type',   
        'grade',       
        'diameter',
        'panjang',
        'quantity',
        'total_volume',
        'volume',
    ];
    
}
