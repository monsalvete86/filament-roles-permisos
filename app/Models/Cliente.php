<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'telefono',
        'email',
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        'aplica_cobertura',
        'fec_nac',
        'direccion',
        'codigopostal',
        'estado_id',
        'condado_id',
        'ciudad_id',
        'estado_migratorio_id',
        'tipo_trabajo',
        'personas_aseguradas',
    ];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function condado(): BelongsTo
    {
        return $this->belongsTo(Condado::class, 'condado_id');
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function estado_migratorio(): BelongsTo
    {
        return $this->belongsTo(EstadoMigratorio::class, 'estado_migratorio_id');
    }

    public function dependiente(): HasMany
    {
        return $this->hasMany(Dependiente::class, 'dependiente');
    }

}
