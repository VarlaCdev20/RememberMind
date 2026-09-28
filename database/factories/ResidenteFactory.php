<?php

namespace Database\Factories;

use App\Models\Residente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Residente>
 */
class ResidenteFactory extends Factory
{
    protected $model = Residente::class;

    public function definition(): array
    {
        return [
            'cod_residente' => 'RES_' . Str::upper(Str::random(10)),
            'nombres' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'numero_documento' => fake()->unique()->numerify('########'),
            'complemento_documento' => null,
            'expedicion_documento' => 'LP',
            'fecha_nacimiento' => fake()->dateTimeBetween('-90 years', '-65 years')->format('Y-m-d'),
            'genero' => fake()->randomElement(['MASCULINO', 'FEMENINO']),
            'estado_civil' => 'SOLTERO/A',
            'telefono' => null,
            'celular' => null,
            'direccion' => fake()->address(),
            'nivel_educativo' => 'SECUNDARIA',
            'grupo_sanguineo' => 'O',
            'factor_rh' => '+',
            'foto' => null,
            'estado' => 'ACTIVO',
            'observacion' => null,
        ];
    }

    public function create($attributes = [], ?\Illuminate\Database\Eloquent\Model $parent = null)
    {
        $def = $this->definition();

        $mapped = [
            'cod_residente' => $attributes['cod_am'] ?? $attributes['cod_residente'] ?? $def['cod_residente'],
            'nombres' => $attributes['nombres'] ?? $def['nombres'],
            'apellido_paterno' => $attributes['ap_paterno'] ?? $attributes['apellido_paterno'] ?? $def['apellido_paterno'],
            'apellido_materno' => $attributes['ap_materno'] ?? $attributes['apellido_materno'] ?? $def['apellido_materno'],
            'numero_documento' => $attributes['ci'] ?? $attributes['numero_documento'] ?? $def['numero_documento'],
            'complemento_documento' => $attributes['complemento_documento'] ?? null,
            'expedicion_documento' => $attributes['expedicion_documento'] ?? 'LP',
            'fecha_nacimiento' => $attributes['fecha_nac'] ?? $attributes['fecha_nacimiento'] ?? $def['fecha_nacimiento'],
            'genero' => $attributes['genero'] ?? $def['genero'],
            'estado_civil' => $attributes['estado_civil'] ?? 'SOLTERO/A',
            'telefono' => $attributes['telefono'] ?? null,
            'celular' => $attributes['celular'] ?? null,
            'direccion' => $attributes['direccion'] ?? $def['direccion'],
            'nivel_educativo' => $attributes['nivel_educativo'] ?? 'SECUNDARIA',
            'grupo_sanguineo' => $attributes['grupo_sanguineo'] ?? 'O',
            'factor_rh' => $attributes['factor_rh'] ?? '+',
            'foto' => $attributes['foto'] ?? null,
            'estado' => $attributes['estado'] ?? 'ACTIVO',
            'observacion' => $attributes['observacion'] ?? null,
        ];

        $modelClass = $this->model;
        return $modelClass::crearDesdeAdmision($mapped);
    }
}