<?php

namespace App\Models;

use App\Backend\Modulos\SistemaExperto\Conocimiento\BuilderExperto;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/** Metadatos comunes; las relaciones se declaran en cada modelo concreto. */
abstract class ModeloExperto extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = ['*'];

    public function newEloquentBuilder($query): BuilderExperto
    {
        return new BuilderExperto($query);
    }

    protected static function booted(): void
    {
        static::deleting(function (): void {
            throw new LogicException('El conocimiento y las ejecuciones expertas conservan su historia; no admiten borrado físico ordinario.');
        });

        static::updating(function (self $modelo): void {
            if ($modelo->isDirty($modelo->getKeyName())) {
                throw new LogicException('La identidad de un registro experto es inmutable.');
            }

            // Una revisión del conocimiento o una reejecución crea registros nuevos.
            // La edición y activación requieren su propio flujo de gobernanza.
            throw new LogicException('Los registros expertos se conservan sin sobrescritura; registra una nueva versión o ejecución.');
        });
    }
}
