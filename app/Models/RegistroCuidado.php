<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RegistroCuidado extends Model
{
    use GeneraCodigo, LogsActivity;

    protected $table = 'registros_cuidados';
    protected $primaryKey = 'cod_registro_cuidado';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'RCD';
    protected $digitsCode = 7;
    protected $guarded = ['cod_registro_cuidado'];
    protected $casts = [
        'fecha_hora_evento' => 'datetime', 'es_continente' => 'boolean',
        'presenta_dificultad' => 'boolean', 'presenta_dolor' => 'boolean',
        'usa_dispositivo' => 'boolean', 'deambulacion_nocturna' => 'boolean', 'agitacion' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn (string $evento) => "Registro de {$this->tipo} {$evento} para {$this->cod_am}.");
    }

    public function adultoMayor() { return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am'); }
    public function turno() { return $this->belongsTo(TurnoEnfermeria::class, 'cod_turno', 'cod_turno'); }
    public function registrador() { return $this->belongsTo(User::class, 'registrado_por', 'cod_usu'); }
    public function original() { return $this->belongsTo(self::class, 'rectifica_a', 'cod_registro_cuidado'); }
}
