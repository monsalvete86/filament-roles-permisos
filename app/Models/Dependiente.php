<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dependiente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_dependiente',
        'aplica_cobertura_dependiente',
        'estado_migratorio_dependiente_id',
        'fec_nac_dependiente',
        'cliente_id',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function estado_migratorio_dependiente(): BelongsTo
    {
        return $this->belongsTo(EstadoMigratorio::class, 'estado_migratorio_dependiente_id');
    }

}
