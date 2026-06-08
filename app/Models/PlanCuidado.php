<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PlanCuidado extends Model
{
    use GeneraCodigo;
    use SoftDeletes, LogsActivity;

    protected $table      = 'planes_cuidado';
    protected $primaryKey = 'cod_plan';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'PLC';
    protected $digitsCode = 5;
    public $timestamps   = true;

    protected $fillable = [
        'cod_am',
        'tipo_plan',
        'version',
        'nivel_cuidado',
        'estado',
        'origen',
        'resumen',
        'fecha_inicio',
        'fecha_fin',
        'creado_por',
        'validado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'version'      => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => "Plan de cuidado {$this->tipo_plan} v{$this->version} creado para {$this->cod_am}.",
                'updated' => "Plan #{$this->cod_plan} actualizado. Estado: {$this->estado}.",
                'deleted' => "Plan #{$this->cod_plan} eliminado.",
                default   => "Plan #{$this->cod_plan} modificado ({$e}).",
            });
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'cod_usu');
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por', 'cod_usu');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(TareaPlanCuidado::class, 'cod_plan', 'cod_plan');
    }

    public function tareasActivas(): HasMany
    {
        return $this->tareas()->whereNotIn('estado', ['ANULADA', 'VENCIDA']);
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoDiario::class, 'cod_plan', 'cod_plan');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function scopePorAdulto($query, string $codAm)
    {
        return $query->where('cod_am', $codAm);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
