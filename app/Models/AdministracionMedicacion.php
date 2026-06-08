<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * AdministracionMedicacion — Registro de administración real de medicamentos.
 *
 * Sin SoftDeletes: estos son registros de trazabilidad clínica que
 * NO deben eliminarse bajo ninguna circunstancia.
 */
class AdministracionMedicacion extends Model
{
    use GeneraCodigo;
    use LogsActivity;

    protected $table = 'administracion_medicacion';
    protected $primaryKey = 'cod_admin_med';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'AME';
    protected $digitsCode = 5;

    public $timestamps = true;

    protected $fillable = [
        'cod_med_adulto',
        'cod_am',
        'fecha',
        'hora_programada',
        'hora_real',
        'administrado',
        'motivo_omision',
        'efecto_observado',
        'observacion',
        'registrado_por',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'hora_programada' => 'datetime:H:i',
        'hora_real'       => 'datetime:H:i',
        'administrado'    => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Administración Medicación')
            ->setDescriptionForEvent(function (string $eventName) {
                $estado = $this->administrado ? 'administrada' : 'no administrada';
                return match ($eventName) {
                    'created' => "Se registró administración de medicación ({$estado}) para el adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizó registro de administración de medicación del adulto mayor {$this->cod_am}.",
                    default   => "Evento '{$eventName}' en administración de medicación del adulto mayor {$this->cod_am}.",
                };
            });
    }

    // ── Relaciones ──────────────────────────

    public function medicacion()
    {
        return $this->belongsTo(MedicacionAdulto::class, 'cod_med_adulto', 'cod_med_adulto');
    }

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }
}
