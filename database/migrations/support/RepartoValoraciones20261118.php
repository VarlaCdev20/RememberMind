<?php

require_once __DIR__.'/ConsolidacionTipada20261116.php';

/** Contratos originales de las cuatro especialidades, congelados en sus migraciones. */
abstract class RepartoValoraciones20261118 extends ConsolidacionTipada20261116
{
    protected const CODIGO = 'cod_valoracion_profesional';

    protected function fuentes(): array
    {
        $clinica = require dirname(__DIR__).'/2026_11_14_000008_consolidate_valoraciones_profesionales.php';
        $pedagogia = require dirname(__DIR__).'/2026_11_16_000001_consolidate_pedagogia.php';
        $origenes = (new ReflectionClass($clinica))->getConstant('ORIGENES');
        $contrato = (new ReflectionClass($pedagogia))->getConstant('FUENTES');
        $tipos = $contrato['valoraciones_profesionales']['columns'];
        $fuentes = [];
        foreach (static::ESPECIALIDADES as $tabla) {
            if ($tabla === 'seguimientos_pedagogicos') {
                $fuentes[$tabla] = $contrato[$tabla];

                continue;
            }
            [$tipo, $campos] = $origenes[$tabla];
            $clave = $campos[0];
            $columnas = [$clave => ['character varying(20)', false, null]];
            foreach (array_slice($campos, 1) as $campo) {
                $columnas[$campo] = $tipos[$campo];
            }
            $nombre = match ($tipo) {
                'PSICOLOGICA' => 'psicologica',
                'NUTRICIONAL' => 'nutricional',
                'FUNCIONAL' => 'funcional',
            };
            $foraneas = [
                [$tabla.'_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']],
                [$tabla.'_cod_personal_foreign', ['cod_personal'], 'personal', ['cod_personal']],
                [$tabla.'_cod_atencion_foreign', ['cod_atencion'], 'atenciones', ['cod_atencion']],
                ['fk_'.$nombre.'_atencion_residente', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']],
            ];
            if ($tipo === 'NUTRICIONAL') {
                $foraneas[] = [$tabla.'_cod_medicion_foreign', ['cod_medicion'], 'mediciones_clinicas', ['cod_medicion_antropometrica']];
            }
            $fuentes[$tabla] = ['type' => $tipo, 'key' => $clave, 'columns' => $columnas, 'foreign' => $foraneas,
                'checks' => [], 'indexes' => []];
        }

        return $fuentes;
    }
}
