<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Collection;

class AreaInstitucional
{
    public $cod_area;
    public $nombre;
    public $tipo_area;
    public $descripcion;
    public $color;
    public $estado;
    public $orden;
    public $responsable_id;
    public $responsable;
    public $usuarios;
    public $usuarios_count;
    public $created_at;
    public $updated_at;

    public function __construct(array $attributes = [])
    {
        $this->cod_area = $attributes['cod_area'] ?? '';
        $this->nombre = $attributes['nombre'] ?? '';
        $this->tipo_area = $attributes['tipo_area'] ?? 'Administrativa';
        $this->descripcion = $attributes['descripcion'] ?? '';
        $this->color = $attributes['color'] ?? '#2F3E5C';
        $this->estado = $attributes['estado'] ?? 'ACTIVA';
        $this->orden = $attributes['orden'] ?? 0;
        $this->responsable_id = $attributes['responsable_id'] ?? null;
        $this->created_at = now();
        $this->updated_at = now();
    }

    public static function allAreas(): Collection
    {
        $areasData = [
            'ARE_0001' => [
                'cod_area' => 'ARE_0001',
                'nombre' => 'DIRECCIÓN GENERAL',
                'tipo_area' => 'Administrativa',
                'descripcion' => 'Dirección general y toma de decisiones estratégicas.',
                'color' => '#2F3E5C',
                'estado' => 'ACTIVA',
                'orden' => 1,
            ],
            'ARE_0002' => [
                'cod_area' => 'ARE_0002',
                'nombre' => 'COORDINACIÓN DE PROGRAMAS Y SERVICIOS',
                'tipo_area' => 'Administrativa',
                'descripcion' => 'Coordinación de programas asistenciales y servicios institucionales.',
                'color' => '#5E6599',
                'estado' => 'ACTIVA',
                'orden' => 2,
            ],
            'ARE_0003' => [
                'cod_area' => 'ARE_0003',
                'nombre' => 'ÁREA ADMINISTRATIVA Y REGISTRO INSTITUCIONAL',
                'tipo_area' => 'Administrativa',
                'descripcion' => 'Gestión administrativa, contable y registro de usuarios.',
                'color' => '#967B66',
                'estado' => 'ACTIVA',
                'orden' => 3,
            ],
            'ARE_0004' => [
                'cod_area' => 'ARE_0004',
                'nombre' => 'ÁREA DE ATENCIÓN MÉDICA',
                'tipo_area' => 'Salud',
                'descripcion' => 'Atención médica general, geriatría, enfermería y fisioterapia.',
                'color' => '#63775B',
                'estado' => 'ACTIVA',
                'orden' => 4,
            ],
            'ARE_0005' => [
                'cod_area' => 'ARE_0005',
                'nombre' => 'ÁREA DE PSICOLOGÍA Y SEGUIMIENTO COGNITIVO',
                'tipo_area' => 'Salud',
                'descripcion' => 'Apoyo psicológico, evaluaciones cognitivas y actividades pedagógicas.',
                'color' => '#9B8B7E',
                'estado' => 'ACTIVA',
                'orden' => 5,
            ],
        ];

        return collect($areasData)->map(function ($data) {
            $area = new self($data);
            
            // Find active users belonging to this area
            $usuarios = User::where('cod_area', $area->cod_area)->get();
            $area->usuarios = $usuarios;
            $area->usuarios_count = $usuarios->count();

            // Find an active supervisor/admin in this area to designate as virtual responsible
            $responsable = $usuarios->filter(fn($u) => $u->hasRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']))->first() ?? $usuarios->first();
            $area->responsable = $responsable;
            $area->responsable_id = $responsable?->cod_usu;

            return $area;
        });
    }

    public static function find($codArea)
    {
        return self::allAreas()->firstWhere('cod_area', $codArea);
    }

    public static function findOrFail($codArea)
    {
        $area = self::find($codArea);
        if (!$area) {
            abort(404, "Área institucional no encontrada.");
        }
        return $area;
    }

    public static function count()
    {
        return self::allAreas()->count();
    }

    public static function query()
    {
        return new class {
            public function with($relations) { return $this; }
            public function withCount($relations) { return $this; }
            public function orderBy($column, $direction = 'asc') { return $this; }
            
            public function where($column, $operator = null, $value = null)
            {
                return $this;
            }

            public function whereNull($column)
            {
                return $this;
            }

            public function whereNotNull($column)
            {
                return $this;
            }

            public function count()
            {
                return AreaInstitucional::count();
            }

            public function get() 
            { 
                return AreaInstitucional::allAreas(); 
            }
            
            public function findOrFail($id) { return AreaInstitucional::findOrFail($id); }
            public function find($id) { return AreaInstitucional::find($id); }
        };
    }

    public static function __callStatic($name, $arguments)
    {
        return self::query()->$name(...$arguments);
    }

    public static function with($relations)
    {
        return self::query()->with($relations);
    }

    public static function withCount($relations)
    {
        return self::query()->withCount($relations);
    }

    public static function activas()
    {
        return new class {
            public function count() { return AreaInstitucional::count(); }
        };
    }

    public static function inactivas()
    {
        return new class {
            public function count() { return 0; }
        };
    }

    public static function doesntHave($relation)
    {
        return new class {
            public function count() { 
                return AreaInstitucional::allAreas()->filter(fn($a) => $a->usuarios_count === 0)->count(); 
            }
        };
    }

    public function update(array $attributes = [])
    {
        return true;
    }

    public static function create(array $attributes = [])
    {
        return new self($attributes);
    }

    public function save()
    {
        return true;
    }
}
