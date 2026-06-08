<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionTurnoAdulto extends Model
{
    use GeneraCodigo;

    protected $table = 'asignaciones_turno_adulto';
    protected $primaryKey = 'cod_asig_turno';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'ATA';
    protected $digitsCode = 5;
    public $timestamps = true;

    protected $fillable = [
        'cod_asig_turno',
        'cod_am',
        'cod_turno',
        'cod_usu_enfermero',
        'cod_habitacion',
        'cod_cama',
        'fecha_inicio',
        'fecha_fin',
        'nivel_supervision',
        'estado',
        'motivo_asignacion',
        'asignado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoEnfermeria::class, 'cod_turno', 'cod_turno');
    }

    public function enfermero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_enfermero', 'cod_usu');
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'cod_habitacion', 'cod_habitacion');
    }

    public function cama(): BelongsTo
    {
        return $this->belongsTo(Cama::class, 'cod_cama', 'cod_cama');
    }

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por', 'cod_usu');
    }
}
