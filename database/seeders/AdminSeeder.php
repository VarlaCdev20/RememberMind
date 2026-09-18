<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['correo' => 'admincasaamandita@gmail.com'],
            [
                'cod_usuario' => 'USU_0001',
                'contrasena'  => Hash::make('CasaAmandita123'),
                'estado'      => 'ACTIVO',
            ]
        );

        $admin->assignRole('SUPERADMINISTRADOR');
    }
}
