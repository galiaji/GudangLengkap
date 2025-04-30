<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoodTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'wood_transaction_id',
        'jenis_kayu_id',
        'grade_id',
        'volume',
        'price',
    ];
}