<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AreaInstitucional extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'areas_institucionales';
    protected $primaryKey = 'cod_area';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_area',
        'nombre',
        'slug',
        'tipo_area',
        'descripcion',
        'responsable_id',
        'imagen_area',
        'roles_sugeridos',
        'modulos_relacionados',
        'color',
        'icono',
        'estado',
        'orden',
        'observaciones',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'roles_sugeridos' => 'array',
        'modulos_relacionados' => 'array',
        'orden' => 'integer',
    ];

    /**
     * Spatie Activitylog implementation
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('AreasInstitucionales')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se creó la nueva área institucional '{$this->nombre}' ({$this->cod_area}).",
                    'updated' => "Se actualizó el área institucional '{$this->nombre}' ({$this->cod_area}).",
                    'deleted' => "Se archivó/eliminó el área institucional '{$this->nombre}' ({$this->cod_area}).",
                    default   => "Área institucional {$this->nombre} modificada ({$eventName}).",
                };
            });
    }

    protected static function booted(): void
    {
        static::creating(function ($area) {
            if (!$area->cod_area) {
                $ultimo = self::where('cod_area', 'like', 'ARE_%')
                    ->orderByDesc('cod_area')
                    ->value('cod_area');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $area->cod_area = 'ARE_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relationship with user who is the head of the area
     */
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id', 'cod_usu');
    }

    /**
     * Relationship with users linked to this area
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'cod_area', 'cod_area');
    }

    /**
     * Relationship with creator
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por', 'cod_usu');
    }

    /**
     * Relationship with updater
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'actualizado_por', 'cod_usu');
    }

    /**
     * Scope for active areas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVA');
    }

    /**
     * Scope for inactive areas
     */
    public function scopeInactivas($query)
    {
        return $query->where('estado', 'INACTIVA');
    }
}
