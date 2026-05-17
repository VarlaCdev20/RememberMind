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
                'cod_usu'     => 'USU_0001',
                'nombres'     => 'Super',
                'ap_paterno'  => 'Administrador',
                'password'    => Hash::make('CasaAmandita123'),
                'estado'      => 'ACTIVO',
            ]
        );

        $admin->assignRole('admin');
    }
}
