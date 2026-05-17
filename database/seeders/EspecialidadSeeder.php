<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especialidad;

class EspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            ['nombre' => 'PSICOLOGÍA'],
            ['nombre' => 'PEDAGOGÍA'],
            ['nombre' => 'FISIOTERAPIA'],
            ['nombre' => 'NUTRICIÓN'],
            ['nombre' => 'ENFERMERÍA'],
            ['nombre' => 'GERIATRÍA'],
        ];

        foreach ($especialidades as $esp) {
            Especialidad::updateOrCreate(
                ['nombre' => $esp['nombre']]
            );
        }
    }
}
