<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeriatricSuiteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sembrar Áreas
        $areas = [
            ['cod_area' => 'ARE_COG', 'nombre' => 'Cognitiva', 'descripcion' => 'Tamizaje de capacidades cognitivas y memoria.'],
            ['cod_area' => 'ARE_AFE', 'nombre' => 'Afectiva', 'descripcion' => 'Evaluación del estado de ánimo y síntomas depresivos.'],
            ['cod_area' => 'ARE_FUN', 'nombre' => 'Funcionamiento', 'descripcion' => 'Evaluación de autonomía, movilidad y agudeza sensorial.'],
            ['cod_area' => 'ARE_NUT', 'nombre' => 'Nutricional', 'descripcion' => 'Tamizaje del estado y riesgo nutricional.'],
            ['cod_area' => 'ARE_SOC', 'nombre' => 'Entorno y red social', 'descripcion' => 'Evaluación de recursos familiares, sociales y seguridad.'],
        ];

        foreach ($areas as $a) {
            DB::table('areas_geriatricas')->updateOrInsert(['cod_area' => $a['cod_area']], $a);
        }

        // 2. Sembrar Instrumentos
        $instrumentos = [
            // Cognitiva
            [
                'cod_instrumento' => 'INS_FLUV', 'cod_area' => 'ARE_COG', 'nombre' => 'Fluencia verbal semántica', 'siglas' => 'FVS',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 30.00, 'descripcion' => 'Prueba de fluidez de vocabulario.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_MICG', 'cod_area' => 'ARE_COG', 'nombre' => 'Mini-Cog', 'siglas' => 'Mini-Cog',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 5.00, 'descripcion' => 'Prueba de tamizaje cognitivo rápido de 3 palabras y dibujo de reloj.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_MMSE', 'cod_area' => 'ARE_COG', 'nombre' => 'MMSE / Mini-Mental', 'siglas' => 'MMSE',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 30.00, 'descripcion' => 'Mini-Mental State Examination.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_MOCA', 'cod_area' => 'ARE_COG', 'nombre' => 'MoCA', 'siglas' => 'MoCA',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 30.00, 'descripcion' => 'Montreal Cognitive Assessment.', 'estado' => 'ACTIVO'
            ],

            // Afectiva
            [
                'cod_instrumento' => 'INS_GD15', 'cod_area' => 'ARE_AFE', 'nombre' => 'GDS-15 (Escala de Yesavage)', 'siglas' => 'GDS-15',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 15.00, 'descripcion' => 'Tamizaje de depresión en el adulto mayor.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_CE07', 'cod_area' => 'ARE_AFE', 'nombre' => 'CESD-7', 'siglas' => 'CESD-7',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 21.00, 'descripcion' => 'Escala de sintomatología depresiva.', 'estado' => 'ACTIVO'
            ],

            // Funcionamiento
            [
                'cod_instrumento' => 'INS_KATZ', 'cod_area' => 'ARE_FUN', 'nombre' => 'Índice de Katz', 'siglas' => 'Katz',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 6.00, 'descripcion' => 'Evaluación de actividades básicas de la vida diaria (ABVD).', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_LAWT', 'cod_area' => 'ARE_FUN', 'nombre' => 'Índice de Lawton', 'siglas' => 'Lawton',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 8.00, 'descripcion' => 'Evaluación de actividades instrumentales de la vida diaria (AIVD).', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_SPPB', 'cod_area' => 'ARE_FUN', 'nombre' => 'SPPB (Short Physical Performance Battery)', 'siglas' => 'SPPB',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 12.00, 'descripcion' => 'Batería corta de rendimiento físico.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_FRAI', 'cod_area' => 'ARE_FUN', 'nombre' => 'Cuestionario FRAIL', 'siglas' => 'FRAIL',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 5.00, 'descripcion' => 'Detección rápida de síndrome de fragilidad.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_TUAG', 'cod_area' => 'ARE_FUN', 'nombre' => 'Timed Up and Go', 'siglas' => 'TUG',
                'tipo_resultado' => 'TIEMPO', 'puntaje_maximo' => null, 'descripcion' => 'Evaluación de movilidad básica y riesgo de caídas.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_SUSU', 'cod_area' => 'ARE_FUN', 'nombre' => 'Prueba del susurro', 'siglas' => 'Susurro',
                'tipo_resultado' => 'CUALITATIVO', 'puntaje_maximo' => null, 'descripcion' => 'Evaluación rápida de la agudeza auditiva.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_RMED', 'cod_area' => 'ARE_FUN', 'nombre' => 'Revisión de la medicación', 'siglas' => 'RevMed',
                'tipo_resultado' => 'CUALITATIVO', 'puntaje_maximo' => null, 'descripcion' => 'Registro y revisión de medicación declarada o indicada.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_VMAR', 'cod_area' => 'ARE_FUN', 'nombre' => 'Velocidad de la marcha', 'siglas' => 'VelMarcha',
                'tipo_resultado' => 'TIEMPO', 'puntaje_maximo' => null, 'descripcion' => 'Cálculo de velocidad para recorrer una distancia estándar.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_PEEK', 'cod_area' => 'ARE_FUN', 'nombre' => 'Agudeza visual Peek Acuity', 'siglas' => 'PeekAcuity',
                'tipo_resultado' => 'CUALITATIVO', 'puntaje_maximo' => null, 'descripcion' => 'Detección de agudeza visual digital.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_SNEL', 'cod_area' => 'ARE_FUN', 'nombre' => 'Agudeza visual Snellen', 'siglas' => 'Snellen',
                'tipo_resultado' => 'FRACCION_VISUAL', 'puntaje_maximo' => null, 'descripcion' => 'Prueba de agudeza visual clásica mediante tabla.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_CBOL', 'cod_area' => 'ARE_FUN', 'nombre' => 'Valoración visual con cartilla de bolsillo', 'siglas' => 'CartillaBolsillo',
                'tipo_resultado' => 'FRACCION_VISUAL', 'puntaje_maximo' => null, 'descripcion' => 'Tamizaje de agudeza visual de lectura.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_BRAD', 'cod_area' => 'ARE_FUN', 'nombre' => 'Escala de Braden', 'siglas' => 'Braden',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 24.00, 'descripcion' => 'Evaluación de riesgo de úlceras por presión.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_NORT', 'cod_area' => 'ARE_FUN', 'nombre' => 'Escala de Norton', 'siglas' => 'Norton',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 20.00, 'descripcion' => 'Valoración del riesgo de úlceras por presión.', 'estado' => 'ACTIVO'
            ],

            // Nutricional
            [
                'cod_instrumento' => 'INS_MNAS', 'cod_area' => 'ARE_NUT', 'nombre' => 'MNA-SF', 'siglas' => 'MNA-SF',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 14.00, 'descripcion' => 'Mini Nutritional Assessment Short Form.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_MUST', 'cod_area' => 'ARE_NUT', 'nombre' => 'MUST', 'siglas' => 'MUST',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 6.00, 'descripcion' => 'Malnutrition Universal Screening Tool.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_SARF', 'cod_area' => 'ARE_NUT', 'nombre' => 'SARC-F', 'siglas' => 'SARC-F',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 10.00, 'descripcion' => 'Tamizaje rápido de sarcopenia.', 'estado' => 'ACTIVO'
            ],

            // Entorno y red social
            [
                'cod_instrumento' => 'INS_BARR', 'cod_area' => 'ARE_SOC', 'nombre' => 'Barreras del entorno físico y movilidad', 'siglas' => 'Barreras',
                'tipo_resultado' => 'CUALITATIVO', 'puntaje_maximo' => null, 'descripcion' => 'Detección de obstáculos físicos en el hogar.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_MALG', 'cod_area' => 'ARE_SOC', 'nombre' => 'Escala geriátrica de maltrato', 'siglas' => 'Maltrato',
                'tipo_resultado' => 'CUANTITATIVO', 'puntaje_maximo' => 22.00, 'descripcion' => 'Detección de riesgo de violencia y negligencia.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_OARS', 'cod_area' => 'ARE_SOC', 'nombre' => 'OARS', 'siglas' => 'OARS',
                'tipo_resultado' => 'MIXTO', 'puntaje_maximo' => null, 'descripcion' => 'Escala de recursos sociales y familiares.', 'estado' => 'ACTIVO'
            ],
            [
                'cod_instrumento' => 'INS_DIVE', 'cod_area' => 'ARE_SOC', 'nombre' => 'Inventario de recursos sociales Díaz-Veiga', 'siglas' => 'Diaz-Veiga',
                'tipo_resultado' => 'MIXTO', 'puntaje_maximo' => null, 'descripcion' => 'Tamizaje de relaciones sociales del adulto mayor.', 'estado' => 'ACTIVO'
            ],
        ];

        foreach ($instrumentos as $i) {
            DB::table('instrumentos_geriatricos')->updateOrInsert(['cod_instrumento' => $i['cod_instrumento']], $i);
        }
    }
}
