<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class NotaEvolucionMedica extends Model
{
    use GeneraCodigo, SoftDeletes, LogsActivity;

    protected $table      = 'notas_evolucion_medica';
    protected $primaryKey = 'cod_nota';
    public    $incrementing = false;
    protected $keyType    = 'string';
    protected $prefixCode = 'NEV';
    protected $digitsCode = 5;

    protected $fillable = [
        'cod_nota', 'cod_am', 'fecha', 'hora', 'tipo_nota',
        'subjetivo', 'objetivo', 'valoracion', 'plan', 'observaciones',
        'pa_sistolica', 'pa_diastolica', 'fc', 'fr',
        'temperatura', 'saturacion', 'glucosa', 'peso',
        'registrado_por', 'estado',
        'motivo_anulacion', 'anulado_por', 'anulado_en',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'temperatura' => 'decimal:1',
        'glucosa'     => 'decimal:1',
        'peso'        => 'decimal:1',
        'anulado_en'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $nota) {
            if (!$nota->cod_nota) {
                $ultimo = self::withTrashed()
                    ->where('cod_nota', 'like', 'NEV_%')
                    ->orderByDesc('cod_nota')
                    ->value('cod_nota');
                $numero = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;
                $nota->cod_nota = 'NEV_' . str_pad($numero, 5, '0', STR_PAD_LEFT);
            }
            $nota->estado ??= 'ACTIVO';
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('NotaEvolucion')
            ->setDescriptionForEvent(fn(string $e) => match ($e) {
                'created' => "Nota de evolución #{$this->cod_nota} registrada para {$this->cod_am}.",
                'updated' => "Nota de evolución #{$this->cod_nota} actualizada.",
                'deleted' => "Nota de evolución #{$this->cod_nota} anulada.",
                default   => "Nota de evolución #{$this->cod_nota} modificada ({$e}).",
            });
    }

    public function adulto()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por', 'cod_usu');
    }

    public function getPaFormateadaAttribute(): ?string
    {
        if ($this->pa_sistolica && $this->pa_diastolica) {
            return "{$this->pa_sistolica}/{$this->pa_diastolica}";
        }
        return null;
    }

    public function scopeActivas($q)
    {
        return $q->where('estado', 'ACTIVO');
    }

    public function scopePorAdulto($q, string $codAm)
    {
        return $q->where('cod_am', $codAm);
    }
}
