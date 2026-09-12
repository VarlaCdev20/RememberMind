<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SignosVitalesAdulto extends Model
{
    use GeneraCodigo;
    use LogsActivity;

    protected $table = 'signos_vitales_adulto';
    protected $primaryKey = 'cod_signo';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'SVA';
    protected $digitsCode = 5;

    public $timestamps = true;

    // Permite personalizar la descripción del log desde el componente antes de save/update
    public string $descripcionLog = '';

    protected $fillable = [
        'cod_am',
        'fecha',
        'hora',
        'presion_arterial',
        'presion_sistolica',
        'presion_diastolica',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'temperatura',
        'saturacion',
        'glucosa',
        'peso',
        'talla',
        'imc',
        'dolor',
        'posicion',
        'usa_oxigeno',
        'valor_atipico_confirmado',
        'observacion',
        'registrado_por',
        'estado',
        'motivo_anulacion',
        'anulado_por',
        'fecha_anulacion',
        'rectifica_a',
        'motivo_rectificacion',
    ];

    protected $casts = [
        'fecha'                  => 'date',
        'frecuencia_cardiaca'    => 'integer',
        'frecuencia_respiratoria'=> 'integer',
        'presion_sistolica'      => 'integer',
        'presion_diastolica'     => 'integer',
        'temperatura'            => 'decimal:1',
        'saturacion'             => 'integer',
        'glucosa'                => 'decimal:2',
        'peso'                   => 'decimal:2',
        'talla'                  => 'decimal:2',
        'imc'                    => 'decimal:2',
        'fecha_anulacion'        => 'datetime',
        'usa_oxigeno'            => 'boolean',
        'valor_atipico_confirmado' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Signos vitales')
            ->setDescriptionForEvent(function (string $eventName) {
                if ($this->descripcionLog !== '') {
                    $desc = $this->descripcionLog;
                    $this->descripcionLog = '';
                    return $desc;
                }
                return match ($eventName) {
                    'created' => "Registró signos vitales del adulto mayor {$this->cod_am}.",
                    'updated' => "Actualizó signos vitales del adulto mayor {$this->cod_am}.",
                    default   => "Modificó signos vitales del adulto mayor {$this->cod_am}.",
                };
            });
    }

    // ── Relaciones ──────────────────────────────────────────────

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    public function anuladoPor()
    {
        return $this->belongsTo(User::class, 'anulado_por', 'cod_usu');
    }

    public function original()
    {
        return $this->belongsTo(self::class, 'rectifica_a', 'cod_signo');
    }

    public function rectificaciones()
    {
        return $this->hasMany(self::class, 'rectifica_a', 'cod_signo');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeVigentes($query)
    {
        return $query->where('estado', 'VIGENTE');
    }

    public function scopeAnulados($query)
    {
        return $query->where('estado', 'ANULADO');
    }

    public function scopePorAdulto($query, string $codAm)
    {
        return $query->where('cod_am', $codAm);
    }

    public function scopeRecientes($query, int $dias = 7)
    {
        return $query->where('fecha', '>=', now()->subDays($dias)->toDateString());
    }

    // ── Accessors ───────────────────────────────────────────────

    public function getPresionFormateadaAttribute(): ?string
    {
        if ($this->presion_sistolica && $this->presion_diastolica) {
            return "{$this->presion_sistolica}/{$this->presion_diastolica}";
        }
        return $this->presion_arterial;
    }

    public function getHoraFormateadaAttribute(): string
    {
        if (!$this->hora) {
            return '—';
        }
        return substr((string) $this->hora, 0, 5);
    }
}
