<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;
    protected static ?string $password;
    protected static array $pendingPersonalAttrs = [];

    public function definition(): array
    {
        return [
            'cod_usuario' => 'USU_'.fake()->unique()->numerify('########'),
            'correo' => fake()->unique()->safeEmail(),
            'contrasena' => static::$password ??= Hash::make('password'),
            'foto' => null,
            'estado' => 'ACTIVO',
        ];
    }

    public function newModel(array $attributes = [])
    {
        $hasExplicitName = isset($attributes['nombres']) || isset($attributes['ap_paterno']) || isset($attributes['apellido_paterno']);

        $personalAttrs = [
            'nombres' => $attributes['nombres'] ?? fake()->firstName(),
            'apellido_paterno' => $attributes['apellido_paterno'] ?? $attributes['ap_paterno'] ?? fake()->lastName(),
            'apellido_materno' => $attributes['apellido_materno'] ?? $attributes['ap_materno'] ?? fake()->lastName(),
            'estado' => $attributes['estado_personal'] ?? 'ACTIVO',
            '_explicit' => $hasExplicitName,
        ];

        unset(
            $attributes['nombres'],
            $attributes['ap_paterno'],
            $attributes['apellido_paterno'],
            $attributes['ap_materno'],
            $attributes['apellido_materno'],
            $attributes['estado_personal'],
            $attributes['_factory_personal_attrs']
        );

        $model = parent::newModel($attributes);
        static::$pendingPersonalAttrs[spl_object_id($model)] = $personalAttrs;

        return $model;
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $attrs = static::$pendingPersonalAttrs[spl_object_id($user)] ?? null;
            unset(static::$pendingPersonalAttrs[spl_object_id($user)]);

            // Auto-crear Personal únicamente si se pasaron explícitamente atributos de nombre y no existe Personal
            if ($attrs && !empty($attrs['_explicit']) && !\App\Models\Personal::where('cod_usuario', $user->cod_usuario)->exists()) {
                $digits = preg_replace('/[^0-9]/', '', (string)$user->cod_usuario);
                $codPersonal = 'PER_' . str_pad(substr($digits, -8) ?: fake()->numerify('########'), 8, '0', STR_PAD_LEFT);

                \App\Models\Personal::create([
                    'cod_personal' => $codPersonal,
                    'cod_usuario' => $user->cod_usuario,
                    'nombres' => $attrs['nombres'],
                    'apellido_paterno' => $attrs['apellido_paterno'],
                    'apellido_materno' => $attrs['apellido_materno'],
                    'numero_documento' => fake()->unique()->numerify('########'),
                    'profesion' => 'ENFERMERIA',
                    'estado' => $attrs['estado'] ?? 'ACTIVO',
                ]);

                $user->unsetRelation('personal');
            }
        });
    }

    public function unverified(): static
    {
        return $this->state([]);
    }
}