<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrador Principal Casa Amandita
        $adminPassword = Str::password(24);
        $admin = User::firstOrCreate(
            ['correo' => 'admincasaamandita@gmail.com'],
            [
                'cod_usuario' => 'USU_0001',
                'contrasena'  => $adminPassword,
                'estado'      => 'ACTIVO',
            ]
        );
        $this->reportInitialPassword($admin, $adminPassword);
        $admin->assignRole('SUPERADMINISTRADOR');

        Personal::updateOrCreate(
            ['cod_usuario' => $admin->cod_usuario],
            [
                'cod_personal' => 'PER_0001',
                'nombres' => 'CARLA',
                'apellido_paterno' => 'ENCINAS',
                'numero_documento' => 'ADMIN-0001',
                'profesion' => 'ADMINISTRACIÓN',
                'fecha_ingreso' => now()->toDateString(),
                'estado' => 'ACTIVO',
            ]
        );

        // 2. Usuario Desarrollador Carla Encinas
        $devPassword = Str::password(24);
        $dev = User::firstOrCreate(
            ['correo' => 'carlaencinas78@gmail.com'],
            [
                'cod_usuario' => 'USU_0002',
                'contrasena'  => $devPassword,
                'estado'      => 'ACTIVO',
            ]
        );
        $this->reportInitialPassword($dev, $devPassword);
        $dev->assignRole('SUPERADMINISTRADOR');

        Personal::updateOrCreate(
            ['cod_usuario' => $dev->cod_usuario],
            [
                'cod_personal' => 'PER_0002',
                'nombres' => 'CARLA',
                'apellido_paterno' => 'ENCINAS',
                'numero_documento' => 'DEV-0001',
                'profesion' => 'ADMINISTRACIÓN',
                'fecha_ingreso' => now()->toDateString(),
                'estado' => 'ACTIVO',
            ]
        );

        // 3. Usuario Enfermería Institucional
        $enfPassword = Str::password(24);
        $enf = User::firstOrCreate(
            ['correo' => 'enfermeria@remembermind.com'],
            [
                'cod_usuario' => 'USU_0003',
                'contrasena'  => $enfPassword,
                'estado'      => 'ACTIVO',
            ]
        );
        $this->reportInitialPassword($enf, $enfPassword);
        $enf->assignRole('ENFERMEROS');

        Personal::updateOrCreate(
            ['cod_usuario' => $enf->cod_usuario],
            [
                'cod_personal' => 'PER_0003',
                'nombres' => 'ROSA',
                'apellido_paterno' => 'MAMANI',
                'numero_documento' => 'ENF-0001',
                'profesion' => 'ENFERMERÍA',
                'fecha_ingreso' => now()->toDateString(),
                'estado' => 'ACTIVO',
            ]
        );
    }

    private function reportInitialPassword(User $user, string $password): void
    {
        if ($user->wasRecentlyCreated) {
            $this->command?->warn("Credencial inicial de {$user->correo}: {$password}");
        }
    }
}
