<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * FamiliarAdulto — Tabla pivot entre familiares y adultos mayores.
 *
 * Estructura de tabla (tras migraciones acumuladas):
 * - id (bigint autoincrement, PK)
 * - cod_fam (string, FK → familiares.cod_fam, NOT NULL)
 * - cod_am (string, FK → adulto_mayor.cod_am, NOT NULL)
 * - parentesco_vinculo (string, nullable)
 * - es_responsable (boolean, default false)
 * - estado (string, default ACTIVO)
 * - observaciones (text, nullable)
 * - created_at, updated_at (timestamps)
 * - deleted_at (softDeletes)
 *
 * UNIQUE: (cod_fam, cod_am)
 */
class FamiliarAdulto extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'familiar_adulto';
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'cod_fam',
        'cod_am',
        'parentesco_vinculo',
        'es_responsable',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'es_responsable' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Familiar Adulto')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created'  => "Se vinculó un familiar al adulto mayor {$this->cod_am}.",
                    'updated'  => "Se actualizó el vínculo familiar del adulto mayor {$this->cod_am}.",
                    'deleted'  => "Se desactivó el vínculo familiar del adulto mayor {$this->cod_am}.",
                    'restored' => "Se restauró el vínculo familiar del adulto mayor {$this->cod_am}.",
                    default    => "Evento '{$eventName}' en vínculo familiar del adulto mayor {$this->cod_am}.",
                };
            });
    }

    /**
     * Relaciones
     */

    public function familiar()
    {
        return $this->belongsTo(Familiar::class, 'cod_fam', 'cod_fam');
    }

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }
}
