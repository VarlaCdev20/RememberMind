<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

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
    use GeneraCodigo;
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

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'FAM';
    protected $digitsCode = 5;

    public $timestamps = true;

    protected $fillable = [
        'cod_fam',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'ci',
        'parentesco',
        'parentesco_vinculo',
        'telefono',
        'celular',
        'correo',
        'direccion',
        'zona',
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
