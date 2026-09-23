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

    public function unverified(): static
    {
        return $this->state([]);
    }
}
