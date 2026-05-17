<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CargoAdministrativo;

class CargoAdministrativoSeeder extends Seeder
{
    public function run(): void
    {
        $cargos = [
            ['nombre' => 'SECRETARIA', 'descripcion' => 'Apoyo administrativo y recepción.'],
            ['nombre' => 'RECEPCIONISTA', 'descripcion' => 'Atención al público y llamadas.'],
            ['nombre' => 'COORDINADORA', 'descripcion' => 'Gestión de equipos y procesos.'],
            ['nombre' => 'AUXILIAR ADMINISTRATIVO', 'descripcion' => 'Apoyo en tareas generales.'],
            ['nombre' => 'OTRO', 'descripcion' => 'Otros cargos no listados.'],
        ];

        foreach ($cargos as $cargo) {
            CargoAdministrativo::updateOrCreate(
                ['nombre' => $cargo['nombre']],
                ['descripcion' => $cargo['descripcion']]
            );
        }
    }
}
