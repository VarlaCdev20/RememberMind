<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoAdulto;

class EstadoAdultoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            ['cod_est_adul' => 1, 'estado' => 'ACTIVO'],
            ['cod_est_adul' => 2, 'estado' => 'ARCHIVADO'],
            ['cod_est_adul' => 3, 'estado' => 'INACTIVO'],
            ['cod_est_adul' => 4, 'estado' => 'SEGUIMIENTO_ESPECIAL'],
            ['cod_est_adul' => 5, 'estado' => 'RETIRADO'],
            ['cod_est_adul' => 6, 'estado' => 'TRASLADADO'],
            ['cod_est_adul' => 7, 'estado' => 'FALLECIDO'],
            ['cod_est_adul' => 8, 'estado' => 'RESTAURADO'],
        ];

        foreach ($estados as $est) {
            EstadoAdulto::updateOrCreate(
                ['estado' => $est['estado']],
                ['cod_est_adul' => $est['cod_est_adul']]
            );
        }
    }
}
