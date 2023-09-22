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
        'estado_civil_conyugue',
        'nombre_conyugue',
        'aplica_covertura_conyugue',
        'dependientes_fuera_pareja',
        'quien_aporta_ingresos',
        'quien_declara_taxes',
        'total_ingresos_gf',
        'estado_cliente',
        // 'digitadora_id',
        'fecha_digitadora',
        // 'benefit_id',
        'fecha_benefit',
        // 'procesador_id',
        'cobertura_ant',
        'codigo_anterior',
        'ultimo_agente',
        'fecha_retiro',
        'agente',
        'inicio_cobertura',
        'fin_cobertura',
        'imagen',
        'nota_benefit',
        'nota_procesador',
        'nota_digitadora',
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

    public function dependientes(): HasMany
    {
        return $this->hasMany(Dependiente::class);
    }

}
