<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DigitadorCoordinador extends Model
{
    use HasFactory;

    protected $table = 'digitadores_coordinadores';

    protected $fillable = [
        'coordinador_id',
        'digitador_id',
    ];

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    public function digitador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'digitador_id');
    }
}
