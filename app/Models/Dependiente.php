<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dependiente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'aplica_cobertura',
        'estado_migratorio_id',
        'fec_nac',
        'cliente_id',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function estado_migratorio(): BelongsTo
    {
        return $this->belongsTo(EstadoMigratorio::class, 'estado_migratorio_id');
    }

}
