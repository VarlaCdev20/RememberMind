<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * EstadoAdulto — Catálogo de estados institucionales del adulto mayor.
 *
 * NOTA TÉCNICA:
 * La migración original creó esta tabla con PK integer autoincrement.
 * Los campos fecha_in, fecha_fin existentes en la tabla NO se usan actualmente;
 * se conservan para compatibilidad pero NO se deben poblar.
 *
 * TODO (FASE 2+): Los cambios de estado se registrarán en la tabla
 * `historial_estado_adulto` que se creará en la siguiente fase.
 * Una vez implementada, evaluar si fecha_in/fecha_fin pueden eliminarse
 * de esta tabla catálogo.
 *
 * @property int    $cod_est_adul  PK autoincrement
 * @property string $estado        Nombre del estado (ACTIVO, INACTIVO, ARCHIVADO, etc.)
 * @property date   $fecha_in      [LEGACY] No usar — será reemplazado por historial_estado_adulto
 * @property date   $fecha_fin     [LEGACY] No usar — será reemplazado por historial_estado_adulto
 */
class EstadoAdulto extends Model
{
    protected $table = 'estado_adulto';
    protected $primaryKey = 'cod_est_adul';

    // PK es integer autoincrement según migración: $table->increments('cod_est_adul')
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'estado',
        'fecha_in',
        'fecha_fin',
    ];

    /**
     * Relación: adultos mayores con este estado.
     */
    public function adultosMayores()
    {
        return $this->hasMany(AdultoMayor::class, 'cod_est_adul', 'cod_est_adul');
    }

    /**
     * Relación legacy: observaciones vinculadas a este estado.
     */
    public function observaciones()
    {
        return $this->hasMany(ObsAdulto::class, 'cod_est_adul', 'cod_est_adul');
    }

    // ── Relaciones FASE 2: Historial de cambios de estado ──

    /**
     * Registros donde este estado fue el estado ANTERIOR (antes del cambio).
     */
    public function historialComoEstadoAnterior()
    {
        return $this->hasMany(HistorialEstadoAdulto::class, 'estado_anterior', 'cod_est_adul');
    }

    /**
     * Registros donde este estado fue el estado NUEVO (después del cambio).
     */
    public function historialComoEstadoNuevo()
    {
        return $this->hasMany(HistorialEstadoAdulto::class, 'estado_nuevo', 'cod_est_adul');
    }
}