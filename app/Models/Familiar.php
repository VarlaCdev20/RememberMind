<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Familiar — Registro de familiares vinculados a adultos mayores.
 *
 * NOTA TÉCNICA:
 * La migración original usa $table->increments('cod_fam') — PK integer autoincrement.
 * Se eliminó el boot() que intentaba generar PKs string FAM_XXXX, ya que contradecía
 * la estructura real de la base de datos.
 */
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Familiar extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Familiares')
            ->setDescriptionForEvent(function (string $eventName) {
                $nombre = $this->usuario ? ($this->usuario->nombres . ' ' . $this->usuario->ap_paterno) : "ID: $this->cod_fam";
                return match ($eventName) {
                    'created' => "Se registró al familiar '{$nombre}'.",
                    'updated' => "Se actualizaron los datos del familiar '{$nombre}'.",
                    'deleted' => "Se eliminó al familiar '{$nombre}'.",
                    default   => "Familiar {$this->cod_fam} modificado ({$eventName}).",
                };
            });
    }

    protected $table = 'familiares';
    protected $primaryKey = 'cod_fam';

    // PK es integer autoincrement según migración: $table->increments('cod_fam')
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'parentesco',
        'direccion',
        'ocupacion',
        'es_responsable',
        'estado',
        'observaciones',
        'cod_usu',
    ];

    /**
     * Relaciones
     */

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function adultosMayores()
    {
        return $this->belongsToMany(
            AdultoMayor::class,
            'familiar_adulto',
            'cod_fam',
            'cod_am'
        )->withPivot([
            'parentesco_vinculo',
            'es_responsable',
            'estado',
            'observaciones',
        ])->withTimestamps();
    }
}