<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ciudad extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'abreviatura',
        'condado_id',
    ];

    public function condado(): BelongsTo
    {
        return $this->belongsTo(Condado::class, 'condado_id');
    }
}
