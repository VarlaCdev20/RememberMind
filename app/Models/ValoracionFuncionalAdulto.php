<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ValoracionFuncionalAdulto extends Model
{
    use LogsActivity;

    protected $table = 'valoracion_funcional_adulto';
    protected $primaryKey = 'cod_val_func';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'cod_am',
        'fecha_valoracion',
        'come_solo',
        'se_bana_solo',
        'se_viste_solo',
        'va_bano_solo',
        'camina_solo',
        'usa_baston',
        'usa_andador',
        'usa_silla_ruedas',
        'baja_vision',
        'baja_audicion',
        'dificultad_hablar',
        'molestia_luz',
        'molestia_ruido',
        'se_asusta_facil',
        'necesita_supervision',
        'nivel_dependencia',
        'observacion',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_valoracion'    => 'date',
        'come_solo'           => 'boolean',
        'se_bana_solo'        => 'boolean',
        'se_viste_solo'       => 'boolean',
        'va_bano_solo'        => 'boolean',
        'camina_solo'         => 'boolean',
        'usa_baston'          => 'boolean',
        'usa_andador'         => 'boolean',
        'usa_silla_ruedas'    => 'boolean',
        'baja_vision'         => 'boolean',
        'baja_audicion'       => 'boolean',
        'dificultad_hablar'   => 'boolean',
        'molestia_luz'        => 'boolean',
        'molestia_ruido'      => 'boolean',
        'se_asusta_facil'     => 'boolean',
        'necesita_supervision' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Valoración Funcional')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se registró valoración funcional del adulto mayor {$this->cod_am} — nivel: {$this->nivel_dependencia}.",
                    'updated' => "Se actualizó valoración funcional del adulto mayor {$this->cod_am}.",
                    default   => "Evento '{$eventName}' en valoración funcional del adulto mayor {$this->cod_am}.",
                };
            });
    }

    // ── Relaciones ──────────────────────────

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }
}
