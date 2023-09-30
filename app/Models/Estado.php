<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo_postal',
        'abreviatura',
    ];

    public function condados(): HasMany
    {
        return $this->hasMany(Condado::class);
    }

}
