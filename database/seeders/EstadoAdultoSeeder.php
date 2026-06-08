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
            ['cod_est_adul' => 'EST_001', 'estado' => 'ACTIVO'],
            ['cod_est_adul' => 'EST_002', 'estado' => 'ARCHIVADO'],
            ['cod_est_adul' => 'EST_003', 'estado' => 'INACTIVO'],
            ['cod_est_adul' => 'EST_004', 'estado' => 'SEGUIMIENTO_ESPECIAL'],
            ['cod_est_adul' => 'EST_005', 'estado' => 'RETIRADO'],
            ['cod_est_adul' => 'EST_006', 'estado' => 'TRASLADADO'],
            ['cod_est_adul' => 'EST_007', 'estado' => 'FALLECIDO'],
            ['cod_est_adul' => 'EST_008', 'estado' => 'RESTAURADO'],
        ];

        foreach ($estados as $est) {
            EstadoAdulto::updateOrCreate(
                ['estado' => $est['estado']],
                ['cod_est_adul' => $est['cod_est_adul']]
            );
        }
    }
}
