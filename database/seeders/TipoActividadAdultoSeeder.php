<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoActividadAdulto;

class TipoActividadAdultoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['tipo' => 'Terapia Ocupacional', 'descripcion' => 'Actividades para el mantenimiento de la autonomía.'],
            ['tipo' => 'Estimulación Cognitiva', 'descripcion' => 'Ejercicios de memoria, atención y lenguaje.'],
            ['tipo' => 'Gimnasia Adaptada', 'descripcion' => 'Actividad física de bajo impacto.'],
            ['tipo' => 'Musicoterapia', 'descripcion' => 'Estimulación sensorial a través de la música.'],
            ['tipo' => 'Ludoterapia', 'descripcion' => 'Actividades recreativas y juegos.'],
            ['tipo' => 'Integración Social', 'descripcion' => 'Talleres grupales y convivencia.'],
        ];

        foreach ($tipos as $tipo) {
            TipoActividadAdulto::updateOrCreate(['tipo' => $tipo['tipo']], $tipo);
        }
    }
}
