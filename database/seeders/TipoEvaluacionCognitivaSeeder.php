<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoEvaluacionCognitiva;

class TipoEvaluacionCognitivaSeeder extends Seeder
{
    public function run(): void
    {
        TipoEvaluacionCognitiva::updateOrCreate(
            ['nombre' => 'MoCA'],
            [
                'descripcion' => 'Montreal Cognitive Assessment. Evaluación breve para detección de deterioro cognitivo.',
                'puntaje_maximo' => 30,
                'punto_corte_normal' => 26,
                'punto_corte_riesgo' => 25,
                'estado' => 'ACTIVO',
            ]
        );

        TipoEvaluacionCognitiva::updateOrCreate(
            ['nombre' => 'MMSE'],
            [
                'descripcion' => 'Mini-Mental State Examination. Evaluación cognitiva general del estado mental.',
                'puntaje_maximo' => 30,
                'punto_corte_normal' => 24,
                'punto_corte_riesgo' => 23,
                'estado' => 'ACTIVO',
            ]
        );
    }
}