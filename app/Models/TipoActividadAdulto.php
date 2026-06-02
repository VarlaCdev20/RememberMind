<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoActividadAdulto extends Model
{
    protected $table      = 'tipo_actividades_adulto';
    protected $primaryKey = 'cod_tipo_act';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = false;

    protected $fillable = [
        'tipo',
        'descripcion',
        // ── Nuevos en Fase 1 ─────────────────────────────────────────────────────
        'categoria',               // COGNITIVA | FISICA | SOCIAL | EMOCIONAL | ESPIRITUAL | EDUCATIVA
        'duracion_estimada_minutos',
        'requiere_profesional',
        'activo',
    ];

    protected $casts = [
        'requiere_profesional'       => 'boolean',
        'activo'                     => 'boolean',
        'duracion_estimada_minutos'  => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────────

    public function actividades(): HasMany
    {
        return $this->hasMany(ActividadAdulto::class, 'cod_tipo_act', 'cod_tipo_act');
    }

    public function actividadesActivas(): HasMany
    {
        return $this->hasMany(ActividadAdulto::class, 'cod_tipo_act', 'cod_tipo_act')
            ->whereNull('deleted_at');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopePorCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', strtoupper($categoria));
    }

    public function scopeRequiereProfesional(Builder $query): Builder
    {
        return $query->where('requiere_profesional', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public static function categorias(): array
    {
        return [
            'COGNITIVA'  => 'Cognitiva',
            'FISICA'     => 'Física',
            'SOCIAL'     => 'Social',
            'EMOCIONAL'  => 'Emocional',
            'ESPIRITUAL' => 'Espiritual',
            'EDUCATIVA'  => 'Educativa',
        ];
    }
}
