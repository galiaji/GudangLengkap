<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoodStock extends Model
{
    use HasFactory;

    /**
     * Tabel yang digunakan oleh model ini.
     *
     * @var string
     */
    protected $table = 'wood_stocks';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'wood_type',   // ubah dari wood_type_id
        'grade',       // ubah dari grade_id
        'diameter',
        'panjang',
        'quantity',
        'total_volume',
        'volume',
    ];
}
