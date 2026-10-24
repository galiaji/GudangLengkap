<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoodType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(WoodStock::class);
    }

    // Hanya jenis kayu yang tidak disembunyikan (untuk form & dashboard)
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }
}
