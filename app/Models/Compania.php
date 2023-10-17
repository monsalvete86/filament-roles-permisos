<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compania extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_companias',
        'direccion',
        'telefono',
        'imeil',
        'codigo',
        'estado_id',
    ];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function planes(): HasMany
    {
        return $this->hasMany(PlanesCompania::class);
    }
}
