<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdultoMayorSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar estados necesarios para la FK de adulto_mayor (evitar duplicados)
        DB::table('estado_adulto')->upsert([
            [
                'estado'     => 'ACTIVO',
                'fecha_in'   => now(),
                'fecha_fin'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'estado'     => 'ARCHIVADO',
                'fecha_in'   => now(),
                'fecha_fin'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['estado'], ['updated_at']);

        // Obtener el ID del estado ACTIVO dinámicamente
        $estadoId = DB::table('estado_adulto')->where('estado', 'ACTIVO')->value('cod_est_adul');

        DB::table('adulto_mayor')->insert([
            [
                'cod_am'       => 'AM_0001',
                'nombres'      => 'María',
                'ap_paterno'   => 'Quispe',
                'ap_materno'   => 'Lopez',
                'ci'           => '1234567',
                'fecha_nac'    => '1945-05-12',
                'genero'       => 'FEMENINO',
                'estado_civil' => 'VIUDO',
                'telefono'     => '77777777',
                'zona'         => 'Miraflores',
                'calle'        => 'Av. Busch',
                'fecha_ing'    => now(),
                'tipo_ing'     => 'REGULAR',
                'permanencia'  => 'PERMANENTE',
                'nivel_educat' => 'PRIMARIA',
                'observaciones'=> 'Paciente estable',
                'cod_est_adul' => $estadoId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        ]);
    }
}