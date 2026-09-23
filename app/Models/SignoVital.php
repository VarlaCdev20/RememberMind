<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignoVital extends ModeloOperativo
{
    protected $table = 'signos_vitales';
    protected $primaryKey = 'cod_signo';

    /**
     * Lista cerrada de columnas V2. Los formularios normalizan sus datos antes
     * de persistirlos; el modelo no acepta ni traduce atributos legacy.
     */
    protected $fillable = [
        'cod_signo', 'cod_residente', 'cod_am', 'presion_arterial', 'cod_personal', 'cod_jornada', 'cod_atencion',
        'fecha_hora', 'presion_sistolica', 'presion_diastolica',
        'frecuencia_cardiaca', 'frecuencia_respiratoria', 'temperatura',
        'saturacion_oxigeno', 'glucemia', 'estado', 'observacion',
    ];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }


    public function setCodAmAttribute($value): void
    {
        $this->attributes['cod_residente'] = $value;
    }

    protected static function booted(): void
    {
        static::creating(function (self $signo): void {
            if (empty($signo->cod_signo)) {
                $signo->cod_signo = 'SGN_' . strtoupper(\Illuminate\Support\Str::random(10));
            }
            if (empty($signo->fecha_hora)) {
                $signo->fecha_hora = now();
            }
            if (empty($signo->cod_residente) && !empty($signo->getAttribute('cod_am'))) {
                $signo->cod_residente = $signo->getAttribute('cod_am');
            }
            if (empty($signo->cod_personal)) {
                $p = \App\Models\Personal::first();
                $signo->cod_personal = $p?->cod_personal ?? 'PER_0001';
            }
        });
    }

    public function setPresionArterialAttribute($value): void
    {
        if (is_string($value) && strpos($value, '/') !== false) {
            [$sis, $dia] = explode('/', $value, 2);
            $this->attributes['presion_sistolica'] = (int)$sis;
            $this->attributes['presion_diastolica'] = (int)$dia;
        }
    }



    public function setFechaAttribute($value): void
    {
        $this->attributes['fecha_hora'] = \Carbon\Carbon::parse($value . ' 00:00:00');
    }

    public function setHoraAttribute($value): void
    {
        if (!empty($this->attributes['fecha_hora'])) {
            $f = \Carbon\Carbon::parse($this->attributes['fecha_hora'])->toDateString();
            $this->attributes['fecha_hora'] = \Carbon\Carbon::parse($f . ' ' . $value);
        }
    }

    public function setRegistradoPorAttribute($value): void
    {
        // Ignored or mapped to cod_personal
    }
    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    /** Nombre usado por algunas vistas históricas; la relación consulta residentes V2. */
    public function adultoMayor(): BelongsTo
    {
        return $this->residente();
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->personal();
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'cod_atencion', 'cod_atencion');
    }

    // Formatos de lectura para la interfaz; no son columnas utilizables en SQL.
    public function getFechaAttribute(): mixed
    {
        return $this->fecha_hora;
    }

    public function getHoraAttribute(): ?string
    {
        return $this->fecha_hora?->format('H:i:s');
    }

    public function getSaturacionAttribute(): mixed
    {
        return $this->saturacion_oxigeno;
    }

    public function getGlucosaAttribute(): mixed
    {
        return $this->glucemia;
    }

    public function getCodAmAttribute(): string
    {
        return (string) $this->cod_residente;
    }

    public function getPresionArterialAttribute(): ?string
    {
        return $this->presion_sistolica !== null && $this->presion_diastolica !== null
            ? "{$this->presion_sistolica}/{$this->presion_diastolica}"
            : null;
    }

    public function getPresionFormateadaAttribute(): ?string
    {
        return $this->presion_arterial;
    }

    public function getHoraFormateadaAttribute(): string
    {
        return $this->fecha_hora?->format('H:i') ?? '—';
    }

    public function scopeVigentes($query)
    {
        return $query->whereIn('estado', ['VIGENTE', 'ACTIVO']);
    }

    public function scopePorResidente($query, string $codResidente)
    {
        return $query->where('cod_residente', $codResidente);
    }

    public function scopeRecientes($query, int $dias = 7)
    {
        return $query->where('fecha_hora', '>=', now()->subDays($dias));
    }
}
