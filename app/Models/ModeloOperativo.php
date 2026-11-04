<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class ModeloOperativo extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'cod_atencion', 'cod_atencion');
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'cod_area', 'cod_area');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'cod_usuario');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $modelo): void {
            if (empty($modelo->getKey())) {
                $modelo->setAttribute($modelo->getKeyName(), app(\App\Compartido\Infraestructura\Persistencia\GeneradorCodigo::class)->siguiente($modelo->getTable(), $modelo->getConnectionName()));
            }
            app(\App\Compartido\Infraestructura\Persistencia\GeneradorCodigo::class)->registrarExplicito($modelo->getTable(), (string) $modelo->getKey(), $modelo->getConnectionName());
        });
        static::deleting(function (): void {
            throw new \LogicException('Los registros operativos conservan historia y no admiten borrado físico ordinario.');
        });
        static::updating(function (self $modelo): void {
            if ($modelo->isDirty($modelo->getKeyName())) {
                throw new \LogicException('La identidad de un registro operativo no se modifica mediante una edición.');
            }
        });
    }
}
