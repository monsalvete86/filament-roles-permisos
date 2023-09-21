<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dependiente extends Model
{
    use HasFactory;

    protected $fillable = [
        'dependiente1',
        'aplica_covertura1',
        'estado_migratorio1',
        'fec_nac1',
        'dependiente2',
        'aplica_covertura2',
        'estado_migratorio2',
        'fec_nac2',
        'dependiente3',
        'aplica_covertura3',
        'estado_migratorio3',
        'fec_nac3',
        'dependiente4',
        'aplica_covertura4',
        'estado_migratorio4',
        'fec_nac4',
        'dependiente_x',
        'aplica_covertura_x',
        'estado_migratorio_x',
        'fec_nac_x',
    ];

}
