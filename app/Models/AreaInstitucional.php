<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AreaInstitucional extends Model
{
    use GeneraCodigo;
    use SoftDeletes;

    protected $table = 'areas_institucionales';
    protected $primaryKey = 'cod_area';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'ARE';
    protected $digitsCode = 4;

    protected $fillable = [
        'cod_area',
        'nombre',
        'slug',
        'tipo_area',
        'descripcion',
        'responsable_id',
        'roles_sugeridos',
        'modulos_relacionados',
        'color',
        'icono',
        'estado',
        'orden',
        'observaciones',
        'imagen_area',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'roles_sugeridos' => 'array',
        'modulos_relacionados' => 'array',
        'orden' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $area) {
            if (! $area->slug && $area->nombre) {
                $area->slug = Str::slug($area->nombre);
            }
        });
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id', 'cod_usu');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'cod_area', 'cod_area');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVA');
    }

    public function scopeInactivas($query)
    {
        return $query->where('estado', 'INACTIVA');
    }

    public static function rolesPorArea(string $codArea): array
    {
        return match ($codArea) {
            'ARE_0001', 'ARE_0002', 'ARE_0003' => ['SUPERADMINISTRADOR', 'ADMINISTRADOR'],
            'ARE_0004' => ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'NUTRICIONISTA', 'FISIOTERAPEUTA'],
            'ARE_0005' => ['PSICOLOGO/A', 'PEDAGOGO'],
            'ARE_0008' => ['VOLUNTARIO'],
            default => [],
        };
    }
}
