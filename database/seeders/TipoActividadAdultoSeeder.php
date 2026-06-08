<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoActividadAdulto;

class TipoActividadAdultoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Terapia Ocupacional', 'descripcion' => 'Actividades para el mantenimiento de la autonomía.'],
            ['nombre' => 'Estimulación Cognitiva', 'descripcion' => 'Ejercicios de memoria, atención y lenguaje.'],
            ['nombre' => 'Gimnasia Adaptada', 'descripcion' => 'Actividad física de bajo impacto.'],
            ['nombre' => 'Musicoterapia', 'descripcion' => 'Estimulación sensorial a través de la música.'],
            ['nombre' => 'Ludoterapia', 'descripcion' => 'Actividades recreativas y juegos.'],
            ['nombre' => 'Integración Social', 'descripcion' => 'Talleres grupales y convivencia.'],
        ];

        foreach ($tipos as $tipo) {
            TipoActividadAdulto::updateOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }
    }
}
