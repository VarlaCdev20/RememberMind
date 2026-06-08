<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoAdulto;

class EstadosAdultoFlujoClinicoSeeder extends Seeder
{
    public function run(): void
    {
        $nuevosEstados = [
            ['cod_est_adul' => 'EST_009', 'estado' => 'PREADMISION'],
            ['cod_est_adul' => 'EST_010', 'estado' => 'PENDIENTE_VALORACION_ENFERMERIA'],
            ['cod_est_adul' => 'EST_011', 'estado' => 'VALORACION_ENFERMERIA_COMPLETADA'],
            ['cod_est_adul' => 'EST_012', 'estado' => 'PENDIENTE_VALORACION_MEDICA'],
            ['cod_est_adul' => 'EST_013', 'estado' => 'VALORACION_MEDICA_COMPLETADA'],
            ['cod_est_adul' => 'EST_014', 'estado' => 'ADMITIDO'],
            ['cod_est_adul' => 'EST_015', 'estado' => 'NO_ADMITIDO'],
            ['cod_est_adul' => 'EST_016', 'estado' => 'DERIVADO'],
            ['cod_est_adul' => 'EST_017', 'estado' => 'OBSERVADO'],
            ['cod_est_adul' => 'EST_018', 'estado' => 'ASIGNADO'],
            ['cod_est_adul' => 'EST_019', 'estado' => 'EN_SEGUIMIENTO_ACTIVO'],
            ['cod_est_adul' => 'EST_020', 'estado' => 'EGRESADO'],
        ];

        foreach ($nuevosEstados as $est) {
            EstadoAdulto::updateOrCreate(
                ['estado' => $est['estado']],
                ['cod_est_adul' => $est['cod_est_adul']]
            );
        }

        $this->command->info('Estados del flujo clínico sincronizados.');
    }
}
