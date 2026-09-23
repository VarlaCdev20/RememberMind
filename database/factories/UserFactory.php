<?php

namespace Database\Factories;

use App\Models\Personal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;
    protected static ?string $password;

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

    public function create($attributes = [], ?\Illuminate\Database\Eloquent\Model $parent = null)
    {
        if (array_key_exists('cod_usu', $attributes)) {
            $attributes['cod_usuario'] = $attributes['cod_usu'];
            unset($attributes['cod_usu']);
        }

        $personalAttrs = [];
        foreach (['nombres', 'ap_paterno', 'ap_materno', 'apellido_paterno', 'apellido_materno', 'ci', 'numero_documento', 'profesion'] as $key) {
            if (array_key_exists($key, $attributes)) {
                $personalAttrs[$key] = $attributes[$key];
                unset($attributes[$key]);
            }
        }

        $user = parent::create($attributes, $parent);

        if (! empty($personalAttrs)) {
            $personal = Personal::firstOrNew(['cod_usuario' => $user->cod_usuario]);
            if (! $personal->exists) {
                $personal->cod_personal = 'PER_' . Str::upper(Str::random(10));
            }
            $personal->nombres = $personalAttrs['nombres'] ?? $personal->nombres ?? fake()->firstName();
            $personal->apellido_paterno = $personalAttrs['ap_paterno'] ?? $personalAttrs['apellido_paterno'] ?? $personal->apellido_paterno ?? fake()->lastName();
            $personal->apellido_materno = $personalAttrs['ap_materno'] ?? $personalAttrs['apellido_materno'] ?? $personal->apellido_materno ?? null;
            $personal->numero_documento = $personalAttrs['numero_documento'] ?? $personalAttrs['ci'] ?? $personal->numero_documento ?? fake()->unique()->numerify('########');
            $personal->profesion = $personalAttrs['profesion'] ?? $personal->profesion ?? 'PERSONAL';
            $personal->estado = $user->estado ?? 'ACTIVO';
            $personal->save();

            $user->unsetRelation('personal');
        }

        return $user;
    }

    public function unverified(): static
    {
        return $this->state([]);
    }

    public function withPersonalTeam(): static
    {
        return $this;
    }
}