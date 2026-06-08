<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TurnoInstitucional extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'turnos_institucionales';
    protected $primaryKey = 'cod_turno';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_turno',
        'nombre',
        'hora_inicio',
        'hora_fin',
        'descripcion',
        'color',
        'estado',
        'observaciones',
        'creado_por',
        'actualizado_por'
    ];

    /**
     * Spatie Activitylog options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('TurnosInstitucionales')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se creó el turno institucional '{$this->nombre}' ({$this->cod_turno}).",
                    'updated' => "Se actualizó el turno institucional '{$this->nombre}' ({$this->cod_turno}).",
                    'deleted' => "Se archivó/eliminó el turno institucional '{$this->nombre}' ({$this->cod_turno}).",
                    default   => "Turno institucional {$this->nombre} modificado ({$eventName}).",
                };
            });
    }

    protected static function booted(): void
    {
        static::creating(function ($turno) {
            if (!$turno->cod_turno) {
                $ultimo = self::withTrashed()
                    ->where('cod_turno', 'like', 'TUR_%')
                    ->orderByDesc('cod_turno')
                    ->value('cod_turno');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $turno->cod_turno = 'TUR_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relationship with assignments
     */
    

    /**
     * Relationship with creators/updaters
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por', 'cod_usu');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'actualizado_por', 'cod_usu');
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function scopeInactivos($query)
    {
        return $query->where('estado', 'INACTIVO');
    }
}
