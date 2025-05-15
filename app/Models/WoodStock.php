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
        'wood_type_id',
        'grade_id',
        'diameter',
        'panjang',
        'quantity',
        'total_volume',
    ];

    /**
     * Relasi ke model WoodType.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function woodType()
    {
        return $this->belongsTo(WoodType::class, 'wood_type_id');
    }

    /**
     * Relasi ke model Grade.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }
}
