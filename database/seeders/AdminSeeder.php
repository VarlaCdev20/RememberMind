<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personal;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrador Principal Casa Amandita
        $admin = User::updateOrCreate(
            ['correo' => 'admincasaamandita@gmail.com'],
            [
                'cod_usuario' => 'USU_0001',
                'contrasena'  => 'CasaAmandita123',
                'estado'      => 'ACTIVO',
            ]
        );
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
        $dev = User::updateOrCreate(
            ['correo' => 'carlaencinas78@gmail.com'],
            [
                'cod_usuario' => 'USU_0002',
                'contrasena'  => 'CasaAmandita123',
                'estado'      => 'ACTIVO',
            ]
        );
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
        $enf = User::updateOrCreate(
            ['correo' => 'enfermeria@remembermind.com'],
            [
                'cod_usuario' => 'USU_0003',
                'contrasena'  => 'CasaAmandita123',
                'estado'      => 'ACTIVO',
            ]
        );
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
}
