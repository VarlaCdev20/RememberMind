<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class IncidenteResidente extends Model
{
    use GeneraCodigo, LogsActivity;

    protected $table = 'incidentes_residente';
    protected $primaryKey = 'cod_incidente';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'INC';
    protected $digitsCode = 6;
    protected $guarded = ['cod_incidente'];
    protected $casts = [
        'fecha_hora_evento' => 'datetime', 'fue_presenciado' => 'boolean', 'lesion' => 'boolean',
        'cambio_cognitivo' => 'boolean', 'medico_informado' => 'boolean',
        'familiar_informado' => 'boolean', 'requiere_seguimiento' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn (string $evento) => "Incidente {$this->tipo} {$evento} para {$this->cod_am}.");
    }

    public function adultoMayor() { return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am'); }
    public function turno() { return $this->belongsTo(TurnoEnfermeria::class, 'cod_turno', 'cod_turno'); }
    public function registrador() { return $this->belongsTo(User::class, 'registrado_por', 'cod_usu'); }
    public function lesiones() { return $this->hasMany(LesionResidente::class, 'cod_incidente', 'cod_incidente'); }
}
